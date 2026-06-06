<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request pour la création d'un profil médecin par l'administrateur.
 *
 * Valide que :
 * - L'utilisateur sélectionné existe et n'a pas déjà un profil médecin
 * - La spécialité sélectionnée existe
 * - La photo respecte les formats et tailles autorisés
 */
class StoreDoctorRequest extends FormRequest
{
    /**
     * Seuls les administrateurs peuvent créer un profil médecin.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Règles de validation pour la création d'un médecin.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'exists:users,id', 'unique:doctors,user_id'],
            'speciality_id' => ['required', 'exists:specialities,id'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address'       => ['nullable', 'string', 'max:255'],
            'bio'           => ['nullable', 'string', 'max:1000'],
            'photo'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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
            'user_id.required'       => 'L\'utilisateur est obligatoire.',
            'user_id.exists'         => 'L\'utilisateur sélectionné n\'existe pas.',
            'user_id.unique'         => 'Cet utilisateur est déjà enregistré comme médecin.',
            'speciality_id.required' => 'La spécialité est obligatoire.',
            'speciality_id.exists'   => 'La spécialité sélectionnée n\'existe pas.',
            'photo.image'            => 'Le fichier doit être une image.',
            'photo.mimes'            => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            'photo.max'              => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
