@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Mes rendez-vous</h2>
        <a href="{{ route('patient.appointments.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Nouveau rendez-vous
        </a>
    </div>

    <div class="space-y-4">
        @forelse($appointments as $appointment)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $appointment->doctor->user->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $appointment->doctor->speciality->name }}</p>
                        <p class="text-sm text-gray-600">{{ $appointment->appointment_date->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right">
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

                @if($appointment->appointment_date > now())
                    <div class="mt-4 space-x-2">
                        <a href="{{ route('patient.appointments.edit', $appointment) }}" class="text-blue-600 hover:text-blue-800">Modifier</a>
                        <form method="POST" action="{{ route('patient.appointments.destroy', $appointment) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Êtes-vous sûr?')">Annuler</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-gray-600">Aucun rendez-vous trouvé.</p>
        @endforelse
    </div>

    {{ $appointments->links() }}
</div>
@endsection
