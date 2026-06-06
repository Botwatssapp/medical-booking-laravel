@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('doctor.appointments.index') }}" class="text-blue-600 hover:text-blue-800 mb-6 inline-block">← Retour</a>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-900">Détails du rendez-vous</h2>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-500">Patient</h3>
                <p class="text-lg text-gray-900">{{ $appointment->patient->name }}</p>
                <p class="text-sm text-gray-600">{{ $appointment->patient->email }}</p>
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
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-gray-500">Notes</h3>
                <p class="text-gray-900">{{ $appointment->notes }}</p>
            </div>
        @endif

        @if($appointment->status === 'pending')
            <div class="mt-6 flex gap-4">
                <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="accepted">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded">Accepter</button>
                </form>
                <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded">Refuser</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
