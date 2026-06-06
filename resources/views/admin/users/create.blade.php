@extends('layouts.admin')

@section('admin-content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Ajouter un utilisateur</h2>

    <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-900">Nom</label>
            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full border rounded-lg px-4 py-2 @error('name') border-red-500 @enderror">
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full border rounded-lg px-4 py-2 @error('email') border-red-500 @enderror">
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Rôle</label>
            <select name="role" class="mt-1 w-full border rounded-lg px-4 py-2 @error('role') border-red-500 @enderror">
                <option value="patient">Patient</option>
                <option value="doctor">Médecin</option>
                <option value="admin">Admin</option>
            </select>
            @error('role') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Mot de passe</label>
            <input type="password" name="password" class="mt-1 w-full border rounded-lg px-4 py-2 @error('password') border-red-500 @enderror">
            @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Ajouter</button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded">Annuler</a>
        </div>
    </form>
</div>
@endsection
