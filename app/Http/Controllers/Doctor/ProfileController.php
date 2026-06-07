<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user   = auth()->user();
        $doctor = $user->doctor;

        return view('doctor.profile.edit', compact('user', 'doctor'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user   = auth()->user();
        $doctor = $user->doctor;

        $validated = $request->validate([
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address'       => ['nullable', 'string', 'max:255'],
            'bio'           => ['nullable', 'string', 'max:1000'],
        ]);

        // — Photo de profil du compte utilisateur —
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->update([
                'profile_image' => $request->file('profile_image')
                    ->store('profile/image', 'public'),
            ]);
        }

        // — Informations médicales du profil docteur —
        if ($doctor) {
            $doctor->update([
                'phone'   => $validated['phone']   ?? $doctor->phone,
                'address' => $validated['address'] ?? $doctor->address,
                'bio'     => $validated['bio']     ?? $doctor->bio,
            ]);
        }

        return redirect()->route('doctor.profile.edit')
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
