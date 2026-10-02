<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request pour la modification d'un profil médecin par l'administrateur.
 *
 * La spécialité est réservée à l'admin. Le médecin édite son profil
 * via `UpdateDoctorProfileRequest` (sans spécialité, sans `user_id`).
 */
class UpdateDoctorRequest extends FormRequest
{
    /**
     * Réservé à l'administrateur (route `admin.doctors.update`).
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'speciality_id' => ['nullable', 'exists:specialities,id'],
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
            'speciality_id.exists' => 'La spécialité sélectionnée n\'existe pas.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            'photo.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
