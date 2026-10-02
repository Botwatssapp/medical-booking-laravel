@extends('layouts.admin')
@section('page-title', request('user_id') ? 'Confirmer le compte médecin' : 'Valider un médecin')
@section('page-subtitle', 'La création du profil médical rend le médecin réservable')

@section('admin-content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('admin.doctors.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87]">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
        Retour aux médecins
    </a>

    @if($users->isEmpty())
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-10 text-center">
            <span class="material-symbols-outlined text-5xl text-[#c2c6d4] block mb-3" aria-hidden="true">person_off</span>
            <p class="font-semibold text-[#0d1c2f]">Aucun compte médecin en attente</p>
            <p class="text-sm text-[#526069] mt-1 mb-4">Un médecin doit d’abord s’inscrire avec le rôle médecin.</p>
            <a href="{{ route('admin.users.create') }}" class="inline-flex px-4 py-2 bg-[#003f87] text-white rounded-xl text-sm font-semibold">
                Créer un utilisateur
            </a>
        </div>
    @else
        @if(request('user_id'))
            <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800">
                Vous configurez le profil médical d’un compte déjà inscrit. La spécialité est obligatoire.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.doctors.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6 space-y-4">
            @csrf

            <div>
                <label for="user_id" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Compte médecin</label>
                <select id="user_id" name="user_id" required
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f] focus:outline-none focus:ring-2 focus:ring-[#003f87]/30">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            {{ (string) old('user_id', request('user_id')) === (string) $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="speciality_id" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Spécialité</label>
                <select id="speciality_id" name="speciality_id" required
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f] focus:outline-none focus:ring-2 focus:ring-[#003f87]/30">
                    <option value="">Choisir une spécialité</option>
                    @foreach($specialties as $specialty)
                        <option value="{{ $specialty->id }}" {{ (string) old('speciality_id') === (string) $specialty->id ? 'selected' : '' }}>
                            {{ $specialty->name }}
                        </option>
                    @endforeach
                </select>
                @error('speciality_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Téléphone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
            </div>

            <div>
                <label for="address" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Adresse du cabinet</label>
                <input type="text" id="address" name="address" value="{{ old('address') }}"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
            </div>

            <div>
                <label for="bio" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Bio</label>
                <textarea id="bio" name="bio" rows="4"
                          class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">{{ old('bio') }}</textarea>
            </div>

            <div>
                <label for="photo" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Photo du cabinet</label>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
                <p class="text-xs text-[#526069] mt-1">JPEG, PNG, GIF — max 2 Mo. Distincte de la photo de compte.</p>
                @error('photo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="px-6 py-3 bg-[#003f87] text-white rounded-xl text-sm font-semibold">
                    Confirmer le profil
                </button>
                <a href="{{ route('admin.doctors.index') }}" class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#526069] text-center">
                    Annuler
                </a>
            </div>
        </form>
    @endif
</div>
@endsection
