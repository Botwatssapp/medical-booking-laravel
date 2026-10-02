@extends('layouts.admin')
@section('page-title', 'Modifier l’utilisateur')
@section('page-subtitle', $user->email)

@section('admin-content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87]">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
        Retour aux utilisateurs
    </a>

    <form method="POST" action="{{ route('admin.users.update', $user) }}"
          class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Nom</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Rôle</label>
            <select id="role" name="role" required class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
                <option value="patient" {{ old('role', $user->role) === 'patient' ? 'selected' : '' }}>Patient</option>
                <option value="doctor" {{ old('role', $user->role) === 'doctor' ? 'selected' : '' }}>Médecin</option>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrateur</option>
            </select>
            @error('role') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <p class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">État du compte</p>
            <p class="text-sm text-[#526069]">
                {{ $user->email_verified_at ? 'Email vérifié le '.$user->email_verified_at->format('d/m/Y') : 'Email non vérifié' }}
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" class="px-6 py-3 bg-[#003f87] text-white rounded-xl text-sm font-semibold">Enregistrer</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#526069] text-center">Annuler</a>
        </div>
    </form>
</div>
@endsection
