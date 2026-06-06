@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Modifier rendez-vous</h2>

    <form method="POST" action="{{ route('patient.appointments.update', $appointment) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-900">Médecin</label>
            <p class="mt-1 text-gray-900">{{ $appointment->doctor->user->name }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Date et heure</label>
            <input type="datetime-local" name="appointment_date" value="{{ $appointment->appointment_date->format('Y-m-d\TH:i') }}" class="mt-1 w-full border rounded-lg px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Notes</label>
            <textarea name="notes" rows="4" class="mt-1 w-full border rounded-lg px-4 py-2">{{ $appointment->notes }}</textarea>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Mettre à jour</button>
            <a href="{{ route('patient.appointments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded">Annuler</a>
        </div>
    </form>
</div>
@endsection
