<?php

namespace App\Http\Controllers\Doctor;

use App\Exceptions\DoctorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDoctorProfileRequest;
use App\Services\DoctorOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly DoctorOnboardingService $onboarding) {}

    public function edit(): View
    {
        $user = auth()->user();
        $doctor = $user->doctor;
        $doctor?->load('speciality');

        return view('doctor.profile.edit', compact('user', 'doctor'));
    }

    public function update(UpdateDoctorProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $doctor = $user->doctor;
        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->update([
                'profile_image' => $request->file('profile_image')
                    ->store('profile/image', 'public'),
            ]);
        }

        if ($doctor) {
            $this->authorize('update', $doctor);

            try {
                $this->onboarding->updateOwnProfile($doctor, $user, $validated);
            } catch (DoctorException $e) {
                return back()->with('error', $e->getMessage());
            }
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
