<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('patient.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'gender'            => ['nullable', 'in:male,female,other'],
            'birth_date'        => ['nullable', 'date', 'before:today'],
            'blood_type'        => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'weight'            => ['nullable', 'numeric', 'min:1', 'max:500'],
            'height'            => ['nullable', 'numeric', 'min:30', 'max:300'],
            'address'           => ['nullable', 'string', 'max:500'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('profile_image')) {
            // Supprime l'ancienne image avant de stocker la nouvelle
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')
                ->store('profile/image', 'public');
        } else {
            unset($validated['profile_image']);
        }

        $user->update($validated);

        return redirect()->route('patient.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    public function removeImage(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->update(['profile_image' => null]);
        }

        return back()->with('success', 'Photo supprimée.');
    }
}
