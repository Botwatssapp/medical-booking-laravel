<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request pour la création d'une spécialité médicale.
 *
 * Valide que :
 * - Le nom est fourni, unique et ne dépasse pas 255 caractères
 * - La description est optionnelle
 */
class StoreSpecialtyRequest extends FormRequest
{
    /**
     * Seuls les administrateurs peuvent créer une spécialité.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Règles de validation pour la création d'une spécialité.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'unique:specialities,name', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'name.required' => 'Le nom de la spécialité est obligatoire.',
            'name.unique'   => 'Cette spécialité existe déjà.',
            'name.max'      => 'Le nom ne doit pas dépasser 255 caractères.',
        ];
    }
}
