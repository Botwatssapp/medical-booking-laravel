@extends('layouts.admin')

@section('admin-content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.appointments.index') }}" class="text-blue-600 hover:text-blue-800">← Retour</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-500">Patient</h3>
                <p class="text-lg text-gray-900">{{ $appointment->patient->name }}</p>
                <p class="text-sm text-gray-600">{{ $appointment->patient->email }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500">Médecin</h3>
                <p class="text-lg text-gray-900">{{ $appointment->doctor->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $appointment->doctor->speciality->name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500">Date et Heure</h3>
                <p class="text-lg text-gray-900">{{ $appointment->appointment_date->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500">Statut</h3>
                <span class="px-3 py-1 rounded-full text-sm font-semibold 
                    {{ match($appointment->status) {
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'accepted' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'cancelled' => 'bg-gray-100 text-gray-800',
                        'completed' => 'bg-blue-100 text-blue-800',
                        'missed' => 'bg-orange-100 text-orange-800',
                    } }}">
                    {{ ucfirst($appointment->status) }}
                </span>
            </div>
        </div>

        @if($appointment->notes)
            <div>
                <h3 class="text-sm font-semibold text-gray-500">Notes</h3>
                <p class="text-gray-900">{{ $appointment->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
