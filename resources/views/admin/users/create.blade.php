@extends('layouts.admin')
@section('page-title', 'Ajouter un utilisateur')
@section('page-subtitle', 'Création d’un compte patient, médecin ou administrateur')

@section('admin-content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87]">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
        Retour aux utilisateurs
    </a>

    <form method="POST" action="{{ route('admin.users.store') }}"
          class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Nom <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 @error('name') border-red-500 @enderror">
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Email <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 @error('email') border-red-500 @enderror">
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Rôle <span class="text-red-600" aria-hidden="true">*</span></label>
            <select id="role" name="role" required
                    class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 @error('role') border-red-500 @enderror">
                <option value="patient" {{ old('role') === 'patient' ? 'selected' : '' }}>Patient</option>
                <option value="doctor" {{ old('role') === 'doctor' ? 'selected' : '' }}>Médecin</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrateur</option>
            </select>
            <p class="text-xs text-[#526069] mt-1">Un compte médecin créé ici reste à valider (profil médical).</p>
            @error('role') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Mot de passe <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 @error('password') border-red-500 @enderror">
            @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Confirmer le mot de passe <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" class="px-6 py-3 bg-[#003f87] text-white rounded-xl text-sm font-semibold">Créer</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#526069] text-center">Annuler</a>
        </div>
    </form>
</div>
@endsection
