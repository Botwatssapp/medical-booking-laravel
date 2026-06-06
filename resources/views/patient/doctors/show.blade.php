@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('patient.doctors.index') }}" class="text-blue-600 hover:text-blue-800 mb-6 inline-block">← Retour</a>

    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex gap-8">
            @if($doctor->photo)
                <img src="{{ asset('storage/' . $doctor->photo) }}" alt="{{ $doctor->user->name }}" class="w-48 h-48 rounded object-cover">
            @endif
            <div>
                <h2 class="text-3xl font-bold text-gray-900">{{ $doctor->user->name }}</h2>
                <p class="text-xl text-blue-600">{{ $doctor->speciality->name }}</p>
                <p class="text-gray-600 mt-4">{{ $doctor->bio }}</p>
                
                @if($doctor->phone)
                    <p class="mt-4"><strong>Téléphone:</strong> {{ $doctor->phone }}</p>
                @endif
                @if($doctor->address)
                    <p><strong>Adresse:</strong> {{ $doctor->address }}</p>
                @endif

                <a href="{{ route('patient.appointments.create') }}" class="mt-6 inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded">
                    Prendre rendez-vous
                </a>
            </div>
        </div>

        @if($doctor->availabilities->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Disponibilités</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($doctor->availabilities as $availability)
                        @if($availability->is_available && $availability->date >= today())
                            <div class="border rounded p-4">
                                <p class="font-semibold">{{ $availability->date->format('d/m/Y') }}</p>
                                <p class="text-sm text-gray-600">{{ $availability->start_time }} - {{ $availability->end_time }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
