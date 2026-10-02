<?php

namespace App\Services;

use App\Exceptions\AvailabilityException;
use App\Models\Availability;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Moteur métier des créneaux de disponibilité.
 *
 * Ne gère pas le cycle de vie des rendez-vous : occupation et libération
 * restent dans AppointmentService.
 */
class AvailabilityService
{
    /**
     * Crée un créneau unique pour le médecin authentifié (jamais depuis un doctor_id de requête).
     */
    public function create(Doctor $doctor, string $date, string $startTime, string $endTime): Availability
    {
        $startTime = $this->normalizeTime($startTime);
        $endTime = $this->normalizeTime($endTime);
        $this->assertValidSlot($date, $startTime, $endTime);

        return DB::transaction(function () use ($doctor, $date, $startTime, $endTime) {
            $this->lockDoctorDay($doctor, $date);
            $this->assertSlotFits($doctor, $date, $startTime, $endTime);

            return $doctor->availabilities()->create([
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_available' => true,
            ]);
        });
    }

    /**
     * Génère des créneaux adjacents sur une plage.
     * Les doublons et chevauchements existants sont ignorés (comportement actuel).
     *
     * @return int Nombre de créneaux réellement créés.
     */
    public function generate(
        Doctor $doctor,
        string $date,
        string $periodStart,
        string $periodEnd,
        int $durationMinutes,
    ): int {
        $this->assertFutureDate($date);

        $periodStart = $this->normalizeTime($periodStart);
        $periodEnd = $this->normalizeTime($periodEnd);

        if (! in_array($durationMinutes, [15, 20, 30, 45, 60], true)) {
            throw new AvailabilityException('Durée invalide. Choisissez parmi 15, 20, 30, 45 ou 60 min.');
        }

        if (strcmp($periodEnd, $periodStart) <= 0) {
            throw new AvailabilityException('La fin de plage doit être après le début.');
        }

        return DB::transaction(function () use ($doctor, $date, $periodStart, $periodEnd, $durationMinutes) {
            $this->lockDoctorDay($doctor, $date);

            [$startH, $startM] = array_map('intval', explode(':', substr($periodStart, 0, 5)));
            [$endH, $endM] = array_map('intval', explode(':', substr($periodEnd, 0, 5)));
            $curMin = $startH * 60 + $startM;
            $endMin = $endH * 60 + $endM;

            $created = 0;

            while ($curMin + $durationMinutes <= $endMin) {
                $slotStart = $this->normalizeTime(sprintf('%02d:%02d', intdiv($curMin, 60), $curMin % 60));
                $slotEnd = $this->normalizeTime(sprintf(
                    '%02d:%02d',
                    intdiv($curMin + $durationMinutes, 60),
                    ($curMin + $durationMinutes) % 60
                ));

                if (! $this->isDuplicate($doctor, $date, $slotStart, $slotEnd)
                    && ! $this->overlaps($doctor, $date, $slotStart, $slotEnd)) {
                    $doctor->availabilities()->create([
                        'date' => $date,
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'is_available' => true,
                    ]);
                    $created++;
                }

                $curMin += $durationMinutes;
            }

            return $created;
        });
    }

    /**
     * Supprime un créneau libre.
     * Refuse si un rendez-vous occupant y est encore lié (historique préservé).
     */
    public function delete(Availability $availability): void
    {
        DB::transaction(function () use ($availability) {
            $locked = Availability::query()
                ->lockForUpdate()
                ->find($availability->id);

            if (! $locked) {
                throw new AvailabilityException('Créneau introuvable.');
            }

            $occupied = $locked->occupyingAppointments()->exists();

            if ($occupied) {
                throw new AvailabilityException('Impossible de supprimer un créneau déjà réservé.');
            }

            $locked->delete();
        });
    }

    private function assertValidSlot(string $date, string $startTime, string $endTime): void
    {
        $this->assertFutureDate($date);

        if (strcmp($endTime, $startTime) <= 0) {
            throw new AvailabilityException('L\'heure de fin doit être après l\'heure de début.');
        }
    }

    private function assertFutureDate(string $date): void
    {
        if (! Carbon::parse($date)->startOfDay()->greaterThan(Carbon::today())) {
            throw new AvailabilityException('La date doit être dans le futur.');
        }
    }

    private function assertSlotFits(Doctor $doctor, string $date, string $startTime, string $endTime): void
    {
        if ($this->isDuplicate($doctor, $date, $startTime, $endTime)) {
            throw new AvailabilityException('Un créneau existe déjà à cette heure pour cette date.');
        }

        if ($this->overlaps($doctor, $date, $startTime, $endTime)) {
            throw new AvailabilityException('Ce créneau chevauche une disponibilité existante.');
        }
    }

    /**
     * Sérialise les créations du même médecin / même jour.
     *
     * Le verrou sur la ligne `doctors` couvre aussi le cas « aucun créneau
     * existant » (un `lockForUpdate` sur un résultat vide ne sérialise rien).
     */
    private function lockDoctorDay(Doctor $doctor, string $date): void
    {
        Doctor::query()->whereKey($doctor->id)->lockForUpdate()->first();

        $doctor->availabilities()
            ->whereDate('date', $date)
            ->lockForUpdate()
            ->get();
    }

    private function isDuplicate(Doctor $doctor, string $date, string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        $query = $doctor->availabilities()
            ->whereDate('date', $date)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    /**
     * Chevauchement strict : [start, end) et [otherStart, otherEnd) s'intersectent.
     * Les créneaux adjacents (fin = début du suivant) sont autorisés.
     */
    private function overlaps(Doctor $doctor, string $date, string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        $query = $doctor->availabilities()
            ->whereDate('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    private function normalizeTime(string $time): string
    {
        $parts = array_map('intval', explode(':', $time));

        return sprintf('%02d:%02d:%02d', $parts[0] ?? 0, $parts[1] ?? 0, $parts[2] ?? 0);
    }
}
