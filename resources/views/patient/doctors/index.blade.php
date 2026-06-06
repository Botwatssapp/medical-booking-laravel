@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Médecins</h2>
    </div>

    <!-- Search and Filter -->
    <form method="GET" action="{{ route('patient.doctors.index') }}" class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex gap-4">
            <input type="text" name="search" placeholder="Rechercher..." value="{{ request('search') }}" class="flex-1 border rounded-lg px-4 py-2">
            <select name="specialty_id" class="border rounded-lg px-4 py-2">
                <option value="">Toutes les spécialités</option>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" {{ request('specialty_id') == $specialty->id ? 'selected' : '' }}>
                        {{ $specialty->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Chercher</button>
        </div>
    </form>

    <!-- Doctors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($doctors as $doctor)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    @if($doctor->photo)
                        <img src="{{ asset('storage/' . $doctor->photo) }}" alt="{{ $doctor->user->name }}" class="w-full h-48 object-cover rounded mb-4">
                    @endif
                    <h3 class="text-lg font-semibold text-gray-900">{{ $doctor->user->name }}</h3>
                    <p class="text-sm text-blue-600">{{ $doctor->speciality->name }}</p>
                    <p class="text-sm text-gray-600 mt-2">{{ Str::limit($doctor->bio, 100) }}</p>
                    <a href="{{ route('patient.doctors.show', $doctor) }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        Voir le profil
                    </a>
                </div>
            </div>
        @empty
            <p class="text-gray-600">Aucun médecin trouvé.</p>
        @endforelse
    </div>

    {{ $doctors->links() }}
</div>
@endsection
