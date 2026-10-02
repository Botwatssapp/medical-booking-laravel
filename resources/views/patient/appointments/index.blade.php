@extends('layouts.app')
@section('title', 'Mes rendez-vous')

@section('content')
<div class="max-w-[1100px] mx-auto px-4 sm:px-8 py-8 sm:py-10">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0d1c2f]">Mes rendez-vous</h1>
            <p class="text-[#526069] mt-1">Suivez le statut de vos consultations.</p>
        </div>
        <a href="{{ route('patient.doctors.index') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
            Nouveau rendez-vous
        </a>
    </div>

    @php
        $statusFilters = [
            ['val' => '', 'label' => 'Tous'],
            ['val' => 'pending', 'label' => 'En attente'],
            ['val' => 'accepted', 'label' => 'Confirmés'],
            ['val' => 'completed', 'label' => 'Terminés'],
            ['val' => 'rejected', 'label' => 'Refusés'],
            ['val' => 'cancelled', 'label' => 'Annulés'],
            ['val' => 'missed', 'label' => 'Non réalisés'],
        ];
        $currentStatus = request('status', '');
        $currentDir = request('direction', 'desc');
    @endphp
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-6">
        <div class="flex flex-wrap gap-2" role="navigation" aria-label="Filtrer par statut">
            @foreach($statusFilters as $f)
                <a href="{{ route('patient.appointments.index', array_filter(['status' => $f['val'], 'direction' => $currentDir])) }}"
                   class="px-3 sm:px-4 py-2 rounded-xl text-sm font-semibold border transition-colors
                          {{ $currentStatus === $f['val']
                                ? 'bg-[#0d1c2f] text-white border-[#0d1c2f]'
                                : 'bg-white border-[#c2c6d4] text-[#526069] hover:border-[#003f87] hover:text-[#003f87]' }}"
                   @if($currentStatus === $f['val']) aria-current="page" @endif>
                    {{ $f['label'] }}
                </a>
            @endforeach
        </div>
        <div class="flex items-center gap-2 text-sm text-[#526069]">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">sort</span>
            <a href="{{ request()->fullUrlWithQuery(['direction' => 'desc', 'page' => 1]) }}"
               class="px-3 py-1.5 rounded-lg border transition-colors {{ $currentDir === 'desc' ? 'bg-[#003f87] text-white border-[#003f87]' : 'bg-white border-[#c2c6d4] hover:border-[#003f87] hover:text-[#003f87]' }}">
                Récent d'abord
            </a>
            <a href="{{ request()->fullUrlWithQuery(['direction' => 'asc', 'page' => 1]) }}"
               class="px-3 py-1.5 rounded-lg border transition-colors {{ $currentDir === 'asc' ? 'bg-[#003f87] text-white border-[#003f87]' : 'bg-white border-[#c2c6d4] hover:border-[#003f87] hover:text-[#003f87]' }}">
                Ancien d'abord
            </a>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($appointments as $appointment)
            @php
                $faded = in_array($appointment->status, ['cancelled', 'rejected', 'missed'], true);
                $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
            @endphp

            <article class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm {{ $faded ? 'opacity-80' : '' }}
                            flex flex-col sm:flex-row sm:items-center gap-4 px-4 sm:px-5 py-4">
                <div class="w-14 h-14 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                    <span class="text-[10px] font-bold uppercase">{{ $mois[$appointment->appointment_date->month - 1] }}</span>
                    <span class="text-xl font-bold leading-tight">{{ $appointment->appointment_date->format('d') }}</span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-[#0d1c2f] text-sm">Dr. {{ $appointment->doctor->user->name }}</p>
                        <p class="text-xs text-[#526069]">{{ $appointment->doctor->speciality->name }}</p>
                        @include('patient.partials.status-badge', ['appointment' => $appointment])
                    </div>
                    <p class="text-xs text-[#526069] mt-1">
                        {{ $appointment->appointment_date->format('d/m/Y') }}
                        ·
                        @if($appointment->availability)
                            {{ substr($appointment->availability->start_time, 0, 5) }} – {{ substr($appointment->availability->end_time, 0, 5) }}
                        @else
                            {{ $appointment->appointment_date->format('H:i') }}
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <a href="{{ route('patient.appointments.show', $appointment) }}"
                       class="flex items-center gap-1.5 px-3 py-2 border border-[#c2c6d4] hover:border-[#003f87] text-[#526069] hover:text-[#003f87] rounded-xl text-xs font-semibold transition-colors">
                        Détail
                    </a>
                    @include('patient.partials.cancel-form', ['appointment' => $appointment])
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-[#e0e7ff] py-16 text-center shadow-sm">
                <span class="material-symbols-outlined text-6xl text-[#c2c6d4] block mb-4" aria-hidden="true">calendar_today</span>
                @if(request()->filled('status'))
                    <p class="font-semibold text-[#0d1c2f] mb-1">Aucun rendez-vous pour ce filtre</p>
                    <p class="text-sm text-[#526069] mb-6">Essayez un autre statut ou affichez tous vos rendez-vous.</p>
                    <a href="{{ route('patient.appointments.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#003f87]">
                        Voir tous les rendez-vous
                    </a>
                @else
                    <p class="font-semibold text-[#0d1c2f] mb-1">Aucun rendez-vous pour le moment</p>
                    <p class="text-sm text-[#526069] mb-6">Réservez un créneau auprès d’un médecin validé.</p>
                    <a href="{{ route('patient.doctors.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
                        Trouver un médecin
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $appointments->links() }}</div>
</div>
@endsection
