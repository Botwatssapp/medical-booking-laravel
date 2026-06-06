@extends('layouts.admin')

@section('admin-content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Éditer spécialité</h2>

    <form method="POST" action="{{ route('admin.specialties.update', $specialty) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-900">Nom</label>
            <input type="text" name="name" value="{{ old('name', $specialty->name) }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Description</label>
            <textarea name="description" rows="4" class="mt-1 w-full border rounded-lg px-4 py-2">{{ old('description', $specialty->description) }}</textarea>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Mettre à jour</button>
            <a href="{{ route('admin.specialties.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded">Annuler</a>
        </div>
    </form>
</div>
@endsection
