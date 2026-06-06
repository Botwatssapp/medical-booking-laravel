@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Mon profil médecin</h2>

    <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        @method('PATCH')

        @if($doctor->photo)
            <div>
                <p class="text-sm text-gray-600 mb-2">Photo actuelle:</p>
                <img src="{{ asset('storage/' . $doctor->photo) }}" alt="Photo" class="w-32 h-32 rounded object-cover">
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-900">Photo de profil</label>
            <input type="file" name="photo" accept="image/*" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Téléphone</label>
            <input type="text" name="phone" value="{{ old('phone', $doctor->phone) }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Adresse</label>
            <input type="text" name="address" value="{{ old('address', $doctor->address) }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Bio</label>
            <textarea name="bio" rows="4" class="mt-1 w-full border rounded-lg px-4 py-2">{{ old('bio', $doctor->bio) }}</textarea>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Mettre à jour</button>
    </form>
</div>
@endsection
