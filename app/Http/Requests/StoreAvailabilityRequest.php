<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation de la création de créneaux (unitaire ou génération).
 *
 * Le médecin propriétaire est toujours l'utilisateur authentifié :
 * `doctor_id` n'est jamais lu depuis la requête.
 */
class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->isDoctor()
            && auth()->user()->doctor !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->input('mode') === 'generate') {
            return [
                'mode' => ['required', 'in:generate'],
                'date' => ['required', 'date', 'after:today'],
                'period_start' => ['required', 'date_format:H:i'],
                'period_end' => ['required', 'date_format:H:i', 'after:period_start'],
                'duration_minutes' => ['required', 'integer', 'in:15,20,30,45,60'],
            ];
        }

        return [
            'mode' => ['nullable', 'in:single'],
            'date' => ['required', 'date', 'after:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.after' => 'La date doit être dans le futur.',
            'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'period_end.after' => 'La fin de plage doit être après le début.',
            'duration_minutes.in' => 'Durée invalide. Choisissez parmi 15, 20, 30, 45 ou 60 min.',
        ];
    }
}
