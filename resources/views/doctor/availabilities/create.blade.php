@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Ajouter disponibilité</h2>

    <form method="POST" action="{{ route('doctor.availabilities.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-900">Date *</label>
            <input type="date" name="date" required class="mt-1 w-full border rounded-lg px-4 py-2">
            @error('date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Heure de début *</label>
            <input type="time" name="start_time" required class="mt-1 w-full border rounded-lg px-4 py-2">
            @error('start_time') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Heure de fin *</label>
            <input type="time" name="end_time" required class="mt-1 w-full border rounded-lg px-4 py-2">
            @error('end_time') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Ajouter</button>
            <a href="{{ route('doctor.availabilities.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded">Annuler</a>
        </div>
    </form>
</div>
@endsection
