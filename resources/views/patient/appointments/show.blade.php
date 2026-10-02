@extends('layouts.app')
@section('title', 'Détail du rendez-vous')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-8 py-8 sm:py-10">

    <a href="{{ route('patient.appointments.index') }}"
       class="text-[#003f87] hover:underline text-sm mb-6 inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_back</span>
        Retour à mes rendez-vous
    </a>

    <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6 sm:p-8">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#0d1c2f]">Détail du rendez-vous</h1>
                <p class="text-sm text-[#526069] mt-1">Informations de votre consultation.</p>
            </div>
            @include('patient.partials.status-badge', ['appointment' => $appointment])
        </div>

        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-xs font-bold uppercase tracking-wider text-[#526069]">Médecin</dt>
                <dd class="mt-1 font-semibold text-[#0d1c2f]">Dr. {{ $appointment->doctor->user->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold uppercase tracking-wider text-[#526069]">Spécialité</dt>
                <dd class="mt-1 text-[#0d1c2f]">{{ $appointment->doctor->speciality->name }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-[#526069]">Date</dt>
                    <dd class="mt-1 text-[#0d1c2f]">{{ $appointment->appointment_date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-[#526069]">Heure</dt>
                    <dd class="mt-1 text-[#0d1c2f]">
                        @if($appointment->availability)
                            {{ substr($appointment->availability->start_time, 0, 5) }} – {{ substr($appointment->availability->end_time, 0, 5) }}
                        @else
                            {{ $appointment->appointment_date->format('H:i') }}
                        @endif
                    </dd>
                </div>
            </div>
            @if($appointment->doctor->address)
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-[#526069]">Lieu</dt>
                    <dd class="mt-1 text-[#0d1c2f]">{{ $appointment->doctor->address }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs font-bold uppercase tracking-wider text-[#526069]">Notes</dt>
                <dd class="mt-1 text-[#0d1c2f] whitespace-pre-line">
                    {{ $appointment->notes ?: 'Aucune note.' }}
                </dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('patient.doctors.show', $appointment->doctor) }}"
               class="inline-flex items-center justify-center px-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#526069] hover:border-[#003f87] hover:text-[#003f87]">
                Voir le médecin
            </a>

            @if(in_array($appointment->status, ['pending', 'accepted'], true) && $appointment->appointment_date->isFuture())
                <a href="{{ route('patient.appointments.edit', $appointment) }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#526069] hover:border-[#003f87] hover:text-[#003f87]">
                    Modifier les notes
                </a>
            @endif

            @include('patient.partials.cancel-form', [
                'appointment' => $appointment,
                'label' => 'Annuler ce rendez-vous',
                'buttonClass' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-sm font-semibold',
            ])
        </div>
    </div>
</div>
@endsection
