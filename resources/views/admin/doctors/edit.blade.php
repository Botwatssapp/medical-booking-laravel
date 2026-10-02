@extends('layouts.admin')
@section('page-title', 'Modifier le profil médecin')
@section('page-subtitle', 'Dr '.$doctor->user->name)

@section('admin-content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('admin.doctors.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87]">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
        Retour aux médecins
    </a>

    <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <p class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Compte lié</p>
            <p class="w-full border border-[#e0e7ff] bg-[#f8faff] rounded-xl px-4 py-3 text-[#526069]">
                {{ $doctor->user->name }} · {{ $doctor->user->email }}
            </p>
            <p class="text-xs text-[#526069] mt-1">Le propriétaire du profil ne peut pas être réassigné.</p>
        </div>

        <div>
            <label for="speciality_id" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Spécialité</label>
            <select id="speciality_id" name="speciality_id"
                    class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" {{ (int) old('speciality_id', $doctor->speciality_id) === $specialty->id ? 'selected' : '' }}>
                        {{ $specialty->name }}
                    </option>
                @endforeach
            </select>
            @error('speciality_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Téléphone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $doctor->phone) }}"
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
        </div>

        <div>
            <label for="address" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Adresse du cabinet</label>
            <input type="text" id="address" name="address" value="{{ old('address', $doctor->address) }}"
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
        </div>

        <div>
            <label for="bio" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Bio</label>
            <textarea id="bio" name="bio" rows="4"
                      class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">{{ old('bio', $doctor->bio) }}</textarea>
        </div>

        <div>
            <label for="photo" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Photo du cabinet</label>
            @if($doctor->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($doctor->photo) }}"
                     alt="Photo actuelle du cabinet" class="w-20 h-20 object-cover rounded-xl mb-2 border border-[#e0e7ff]">
            @endif
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif"
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
            @error('photo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" class="px-6 py-3 bg-[#003f87] text-white rounded-xl text-sm font-semibold">
                Enregistrer
            </button>
            <a href="{{ route('admin.doctors.index') }}" class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#526069] text-center">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
