<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request pour la modification d'une spécialité médicale.
 *
 * Valide l'unicité du nom en excluant la spécialité courante pour éviter
 * un faux conflit lors d'une sauvegarde sans changement de nom.
 */
class UpdateSpecialtyRequest extends FormRequest
{
    /**
     * Seuls les administrateurs peuvent modifier une spécialité.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Règles de validation pour la modification d'une spécialité.
     *
     * La règle unique ignore la spécialité en cours de modification
     * via l'ID récupéré depuis le paramètre de route.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Récupère l'ID depuis le paramètre de route 'specialty' (modèle Eloquent)
        $specialtyId = $this->route('specialty')?->id;

        return [
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('specialities', 'name')->ignore($specialtyId),
            ],
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
