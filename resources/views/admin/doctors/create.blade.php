@extends('layouts.admin')

@section('admin-content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">
        {{ request('user_id') ? 'Confirmer le compte médecin' : 'Ajouter un médecin' }}
    </h2>

    @if(request('user_id'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-6 text-sm text-blue-800">
            Vous êtes en train de configurer le profil médical d'un compte déjà existant.
            L'utilisateur sélectionné ci-dessous sera lié à ce profil.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.doctors.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-900">Utilisateur</label>
            <select name="user_id" class="mt-1 w-full border rounded-lg px-4 py-2">
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ (old('user_id', request('user_id')) == $user->id) ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Spécialité</label>
            <select name="speciality_id" class="mt-1 w-full border rounded-lg px-4 py-2">
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Téléphone</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Adresse</label>
            <input type="text" name="address" value="{{ old('address') }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Bio</label>
            <textarea name="bio" rows="4" class="mt-1 w-full border rounded-lg px-4 py-2">{{ old('bio') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Photo</label>
            <input type="file" name="photo" accept="image/*" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Ajouter</button>
            <a href="{{ route('admin.doctors.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded">Annuler</a>
        </div>
    </form>
</div>
@endsection
