<?php

namespace App\Http\Requests;

use App\Models\Availability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request pour la création d'un rendez-vous par un patient.
 *
 * Valide les données soumises et applique les règles métier :
 * - Le créneau doit exister et être disponible
 * - Le créneau doit appartenir au médecin sélectionné
 * - Le patient ne doit pas avoir de rendez-vous sur ce créneau
 */
class StoreAppointmentRequest extends FormRequest
{
    /**
     * Seuls les patients connectés peuvent créer un rendez-vous.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPatient();
    }

    /**
     * Règles de validation avec vérifications des règles métier.
     *
     * `appointment_date` n'est PAS validé ici : il est dérivé côté serveur
     * dans `AppointmentController::store()` à partir de la disponibilité
     * (availability.date + availability.start_time). Cela évite toute
     * manipulation côté client et garantit la cohérence des données.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => [
                'required',
                'exists:doctors,id',
            ],
            'availability_id' => [
                'required',
                // Le créneau doit exister, être disponible, être futur
                // et appartenir au médecin sélectionné
                Rule::exists('availabilities', 'id')->where(function ($query) {
                    $query->where('is_available', true)
                          ->where('date', '>=', now()->toDateString())
                          ->where('doctor_id', $this->input('doctor_id'));
                }),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
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
            'doctor_id.required'       => 'Le médecin est obligatoire.',
            'doctor_id.exists'         => 'Le médecin sélectionné n\'existe pas.',
            'availability_id.required' => 'Veuillez sélectionner un créneau horaire.',
            'availability_id.exists'   => 'Ce créneau n\'est plus disponible ou n\'appartient pas au médecin sélectionné.',
        ];
    }
}
