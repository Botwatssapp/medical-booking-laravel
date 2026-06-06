@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Mes rendez-vous</h2>
    </div>

    <div class="space-y-4">
        @forelse($appointments as $appointment)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $appointment->patient->name }}</h3>
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

                <div class="mt-4 space-x-2">
                    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="text-blue-600 hover:text-blue-800">Voir</a>
                    @if($appointment->status === 'pending')
                        <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="text-green-600 hover:text-green-800">Accepter</button>
                        </form>
                        <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-red-600 hover:text-red-800">Refuser</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-600">Aucun rendez-vous trouvé.</p>
        @endforelse
    </div>

    {{ $appointments->links() }}
</div>
@endsection
