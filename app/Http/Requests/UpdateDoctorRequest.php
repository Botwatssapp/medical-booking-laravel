<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request pour la modification d'un profil médecin.
 *
 * L'administrateur peut modifier tous les champs, y compris la spécialité.
 * Le médecin peut modifier ses informations personnelles (phone, address, bio, photo)
 * mais pas sa spécialité (réservée à l'administrateur).
 */
class UpdateDoctorRequest extends FormRequest
{
    /**
     * Seuls les administrateurs et le médecin concerné peuvent modifier.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isDoctor());
    }

    /**
     * Règles de validation adaptées au rôle de l'utilisateur.
     *
     * Un médecin ne peut pas changer sa propre spécialité.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio'     => ['nullable', 'string', 'max:1000'],
            'photo'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        // Seul l'admin peut modifier la spécialité
        if (auth()->user()->isAdmin()) {
            $rules['speciality_id'] = ['nullable', 'exists:specialities,id'];
        }

        return $rules;
    }

    /**
     * Messages d'erreur personnalisés en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'speciality_id.exists' => 'La spécialité sélectionnée n\'existe pas.',
            'photo.image'          => 'Le fichier doit être une image.',
            'photo.mimes'          => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            'photo.max'            => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
