<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request pour la modification d'un rendez-vous.
 *
 * Les règles de validation varient selon le rôle :
 * - Patient : peut uniquement annuler (status = cancelled) ou modifier ses notes
 * - Médecin : peut changer le statut (accepted, rejected, completed, missed)
 * - Admin : accès complet
 */
class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Seuls les patients, médecins et admins connectés peuvent modifier.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->isPatient() ||
            auth()->user()->isDoctor()  ||
            auth()->user()->isAdmin()
        );
    }

    /**
     * Règles de validation adaptées au rôle de l'utilisateur.
     *
     * Un patient peut uniquement annuler son rendez-vous.
     * Un médecin peut changer le statut vers accepted, rejected, completed ou missed.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = auth()->user();

        if ($user->isPatient()) {
            // Un patient peut uniquement annuler son rendez-vous
            return [
                'status' => ['nullable', Rule::in([Appointment::STATUS_CANCELLED])],
                'notes'  => ['nullable', 'string', 'max:1000'],
            ];
        }

        // Médecin ou Admin : contrôle complet du statut
        return [
            'status' => [
                'nullable',
                Rule::in([
                    Appointment::STATUS_ACCEPTED,
                    Appointment::STATUS_REJECTED,
                    Appointment::STATUS_COMPLETED,
                    Appointment::STATUS_MISSED,
                    Appointment::STATUS_CANCELLED,
                ]),
            ],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'appointment_date' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Messages d'erreur personnalisés en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in'                => 'Le statut sélectionné est invalide.',
            'appointment_date.date'    => 'La date doit être valide.',
            'appointment_date.after'   => 'La date doit être dans le futur.',
        ];
    }
}
