<?php

namespace App\Http\Controllers\Doctor;

use App\Exceptions\AvailabilityException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvailabilityRequest;
use App\Models\Availability;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des disponibilités côté médecin.
 *
 * Orchestration uniquement : autorisation, validation, appel du service.
 */
class AvailabilityController extends Controller
{
    public function __construct(private readonly AvailabilityService $availabilities) {}

    /**
     * Affiche la liste paginée des disponibilités du médecin connecté.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Availability::class);

        $doctor = auth()->user()->doctor;

        $allowedSorts = ['date', 'is_available'];
        $sort = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'date';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $availQuery = $doctor->availabilities()->orderBy($sort, $direction);
        if ($sort === 'date') {
            $availQuery->orderBy('start_time', $direction);
        }
        $availabilities = $availQuery->paginate(20)->withQueryString();

        $counts = $doctor->availabilities()
            ->selectRaw('COUNT(*) as total, SUM(is_available = 1) as available, SUM(is_available = 0) as booked')
            ->first();

        $stats = [
            'total' => (int) ($counts->total ?? 0),
            'available' => (int) ($counts->available ?? 0),
            'booked' => (int) ($counts->booked ?? 0),
        ];

        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $weekSlots = $doctor->availabilities()
            ->whereDate('date', '>=', $weekStart->toDateString())
            ->whereDate('date', '<=', $weekEnd->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('doctor.availabilities.index', compact('availabilities', 'stats', 'weekStart', 'weekSlots'));
    }

    /**
     * Affiche le formulaire de création d'un créneau de disponibilité.
     */
    public function create(): View
    {
        $this->authorize('create', Availability::class);

        $doctor = auth()->user()->doctor()->with(['user', 'speciality'])->firstOrFail();

        $existingSlots = $doctor->availabilities()
            ->whereDate('date', '>', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get(['date', 'start_time', 'end_time'])
            ->map(fn (Availability $slot) => [
                'date' => $slot->date->toDateString(),
                'start' => substr((string) $slot->start_time, 0, 5),
                'end' => substr((string) $slot->end_time, 0, 5),
            ])
            ->values();

        return view('doctor.availabilities.create', compact('doctor', 'existingSlots'));
    }

    /**
     * Enregistre un créneau unique ou une génération de créneaux.
     *
     * `doctor_id` est toujours dérivé du médecin authentifié.
     */
    public function store(StoreAvailabilityRequest $request): RedirectResponse
    {
        $this->authorize('create', Availability::class);

        $doctor = $request->user()->doctor;
        $data = $request->validated();

        try {
            if (($data['mode'] ?? null) === 'generate') {
                $created = $this->availabilities->generate(
                    $doctor,
                    $data['date'],
                    $data['period_start'],
                    $data['period_end'],
                    (int) $data['duration_minutes'],
                );

                return redirect()->route('doctor.availabilities.index')
                    ->with('success', "$created créneau(x) créé(s) avec succès.");
            }

            $this->availabilities->create(
                $doctor,
                $data['date'],
                $data['start_time'],
                $data['end_time'],
            );
        } catch (AvailabilityException $e) {
            $field = ($data['mode'] ?? null) === 'generate' ? 'period_start' : 'start_time';

            return back()
                ->withErrors([$field => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('doctor.availabilities.index')
            ->with('success', 'Créneau créé avec succès.');
    }

    /**
     * Supprime un créneau libre appartenant au médecin connecté.
     */
    public function destroy(Availability $availability): RedirectResponse
    {
        $this->authorize('delete', $availability);

        try {
            $this->availabilities->delete($availability);
        } catch (AvailabilityException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('doctor.availabilities.index')
            ->with('success', 'Disponibilité supprimée avec succès.');
    }
}
