@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Mon profil patient</h2>

    <form method="POST" action="{{ route('patient.profile.update') }}" class="bg-white rounded-lg shadow p-6 space-y-4 max-w-2xl">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-sm font-medium text-gray-900">Nom</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Téléphone</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Adresse</label>
            <input type="text" name="address" value="{{ old('address', $user->address ?? '') }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Mettre à jour</button>
    </form>
</div>
@endsection
