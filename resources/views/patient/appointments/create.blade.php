@extends('layouts.app')
@section('title', 'Confirmer le rendez-vous')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-8 py-8 sm:py-10">

    @if(!$availability)
        <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm p-10 text-center">
            <span class="material-symbols-outlined text-5xl text-[#c2c6d4] block mb-3" aria-hidden="true">event_busy</span>
            <p class="text-[#0d1c2f] font-medium">Aucun créneau disponible n’a été sélectionné.</p>
            <p class="text-sm text-[#526069] mt-1">Le créneau a peut-être déjà été réservé. Choisissez un autre horaire.</p>
            <a href="{{ route('patient.doctors.index') }}"
               class="mt-4 inline-block text-[#003f87] hover:underline text-sm">
                Choisir un médecin
            </a>
        </div>
    @else

        <a href="{{ route('patient.doctors.show', $availability->doctor) }}"
           class="text-[#003f87] hover:underline text-sm mb-6 inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_back</span>
            Retour aux disponibilités
        </a>

        <h1 class="text-2xl font-bold text-[#0d1c2f] mb-2">Confirmer la demande</h1>
        <p class="text-sm text-[#526069] mb-6">
            Votre demande sera transmise au médecin. Le rendez-vous restera
            <strong class="text-[#0d1c2f]">en attente</strong> jusqu’à confirmation.
        </p>

        <div class="bg-[#eff4ff] border border-[#003f87]/20 rounded-xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#003f87] text-white flex items-center justify-center text-xl font-bold shrink-0" aria-hidden="true">
                    {{ strtoupper(substr($availability->doctor->user->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <p class="font-bold text-[#0d1c2f] text-lg">Dr. {{ $availability->doctor->user->name }}</p>
                    <p class="text-[#003f87] text-sm">{{ $availability->doctor->speciality->name }}</p>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-[#526069]">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">calendar_today</span>
                            {{ $availability->date->format('d/m/Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">schedule</span>
                            {{ substr($availability->start_time, 0, 5) }} – {{ substr($availability->end_time, 0, 5) }}
                        </span>
                    </div>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                    Disponible
                </span>
            </div>
        </div>

        <form method="POST"
              action="{{ route('patient.appointments.store') }}"
              class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm p-6 sm:p-8">
            @csrf

            <input type="hidden" name="doctor_id" value="{{ $availability->doctor_id }}">
            <input type="hidden" name="availability_id" value="{{ $availability->id }}">

            <div class="mb-6">
                <label for="appointment-notes" class="block text-sm font-medium text-[#0d1c2f] mb-2">
                    Motif de consultation
                    <span class="text-[#526069] font-normal">(optionnel)</span>
                </label>
                <textarea id="appointment-notes"
                          name="notes"
                          rows="4"
                          maxlength="1000"
                          placeholder="Décrivez brièvement le motif de votre consultation"
                          aria-describedby="{{ $errors->has('notes') ? 'notes-error' : 'notes-help' }}"
                          class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87]
                                 bg-[#f8f9ff] outline-none text-sm resize-none">{{ old('notes') }}</textarea>
                <p id="notes-help" class="text-xs text-[#526069] mt-1">Ces informations seront visibles par le médecin.</p>
                @error('notes')
                    <p id="notes-error" class="text-red-700 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                        class="flex-1 bg-[#003f87] text-white py-3 rounded-xl font-semibold hover:opacity-90 transition-opacity">
                    Envoyer la demande
                </button>
                <a href="{{ route('patient.doctors.show', $availability->doctor) }}"
                   class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-[#526069]
                          hover:bg-[#f8f9ff] transition-colors text-center">
                    Retour
                </a>
            </div>
        </form>

    @endif
</div>
@endsection
