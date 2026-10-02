@extends('layouts.app')
@section('title', 'Modifier les notes')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-8 py-8 sm:py-10">

    <a href="{{ route('patient.appointments.show', $appointment) }}"
       class="text-[#003f87] hover:underline text-sm mb-6 inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_back</span>
        Retour au détail
    </a>

    <h1 class="text-2xl font-bold text-[#0d1c2f] mb-2">Modifier les notes</h1>
    <p class="text-sm text-[#526069] mb-6">
        La date et l’heure ne peuvent pas être changées ici. Pour un autre créneau, annulez ce rendez-vous puis réservez à nouveau.
    </p>

    <form method="POST" action="{{ route('patient.appointments.update', $appointment) }}"
          class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6 sm:p-8 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#526069]">Médecin</p>
            <p class="mt-1 font-semibold text-[#0d1c2f]">Dr. {{ $appointment->doctor->user->name }}</p>
            <p class="text-sm text-[#526069]">{{ $appointment->doctor->speciality->name }}</p>
        </div>

        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#526069]">Date et heure</p>
            <p class="mt-1 text-[#0d1c2f]">
                {{ $appointment->appointment_date->format('d/m/Y') }}
                ·
                @if($appointment->availability)
                    {{ substr($appointment->availability->start_time, 0, 5) }} – {{ substr($appointment->availability->end_time, 0, 5) }}
                @else
                    {{ $appointment->appointment_date->format('H:i') }}
                @endif
            </p>
        </div>

        <div>
            <label for="appointment-notes" class="block text-sm font-medium text-[#0d1c2f] mb-2">Notes</label>
            <textarea id="appointment-notes" name="notes" rows="4" maxlength="1000"
                      class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#003f87]/30">{{ old('notes', $appointment->notes) }}</textarea>
            @error('notes')
                <p class="text-red-700 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 bg-[#003f87] hover:opacity-90 text-white px-6 py-3 rounded-xl font-semibold">
                Enregistrer les notes
            </button>
            <a href="{{ route('patient.appointments.show', $appointment) }}"
               class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-[#526069] text-center">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
