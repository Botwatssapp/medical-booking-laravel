@extends('layouts.admin')

@section('admin-content')
<div class="space-y-6">
    <h2 class="text-3xl font-bold text-gray-900">Rendez-vous</h2>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Patient</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Médecin</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $appointment)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $appointment->patient->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $appointment->doctor->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $appointment->appointment_date->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
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
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.appointments.show', $appointment) }}" class="text-blue-600 hover:text-blue-800">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun rendez-vous trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $appointments->links() }}
</div>
@endsection
