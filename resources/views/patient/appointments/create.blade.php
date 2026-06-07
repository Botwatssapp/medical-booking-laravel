@extends('layouts.app')
@section('title', 'Confirmer le rendez-vous')

@section('content')
<div class="max-w-2xl mx-auto px-8 py-10">

    @if(!$availability)
        {{-- Aucun créneau sélectionné --}}
        <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm p-10 text-center">
            <span class="material-symbols-outlined text-5xl text-[#c2c6d4] block mb-3">event_busy</span>
            <p class="text-[#424752] font-medium">Aucun créneau sélectionné.</p>
            <a href="{{ route('patient.doctors.index') }}"
               class="mt-4 inline-block text-[#003f87] hover:underline text-sm">
                Choisir un médecin
            </a>
        </div>
    @else

        <a href="{{ route('patient.doctors.show', $availability->doctor) }}"
           class="text-[#003f87] hover:underline text-sm mb-6 inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Retour aux disponibilités
        </a>

        <h1 class="text-2xl font-bold text-[#0d1c2f] mb-6">Confirmer le rendez-vous</h1>

        {{-- Récapitulatif du créneau sélectionné --}}
        <div class="bg-[#eff4ff] border border-[#003f87]/20 rounded-xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#003f87] text-white flex items-center justify-center text-xl font-bold shrink-0">
                    {{ strtoupper(substr($availability->doctor->user->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <p class="font-bold text-[#0d1c2f] text-lg">Dr. {{ $availability->doctor->user->name }}</p>
                    <p class="text-[#003f87] text-sm">{{ $availability->doctor->speciality->name }}</p>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-[#526069]">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            {{ $availability->date->format('d/m/Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            {{ substr($availability->start_time, 0, 5) }} – {{ substr($availability->end_time, 0, 5) }}
                        </span>
                    </div>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                    Disponible
                </span>
            </div>
        </div>

        {{-- Formulaire de confirmation --}}
        <form method="POST"
              action="{{ route('patient.appointments.store') }}"
              class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm p-8">
            @csrf

            {{-- Champs cachés : vérifiés et validés côté serveur --}}
            <input type="hidden" name="doctor_id"       value="{{ $availability->doctor_id }}">
            <input type="hidden" name="availability_id" value="{{ $availability->id }}">

            {{-- Motif / Notes --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-[#0d1c2f] mb-2">
                    Motif de consultation
                    <span class="text-[#526069] font-normal">(optionnel)</span>
                </label>
                <textarea name="notes"
                          rows="4"
                          placeholder="Décrivez brièvement le motif de votre consultation..."
                          class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87]
                                 bg-[#f8f9ff] outline-none text-sm resize-none">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Erreurs de validation --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex gap-4">
                <button type="submit"
                        class="flex-1 bg-[#003f87] text-white py-3 rounded-xl font-semibold
                               hover:bg-[#0056b3] transition-all">
                    Confirmer le rendez-vous
                </button>
                <a href="{{ route('patient.doctors.show', $availability->doctor) }}"
                   class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-[#526069]
                          hover:bg-[#f8f9ff] transition-all text-center">
                    Annuler
                </a>
            </div>
        </form>

    @endif
</div>
@endsection
