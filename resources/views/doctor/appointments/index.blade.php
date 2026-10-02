@extends('layouts.doctor')
@section('page-title', 'Mes rendez-vous')
@section('page-subtitle', 'Gérez et suivez les demandes de vos patients')

@section('doctor-content')
<div class="space-y-5">

    @php
        $statuses = [
            ['val' => '', 'label' => 'Tous'],
            ['val' => 'pending', 'label' => 'En attente'],
            ['val' => 'accepted', 'label' => 'Confirmés'],
            ['val' => 'completed', 'label' => 'Terminés'],
            ['val' => 'rejected', 'label' => 'Refusés'],
            ['val' => 'cancelled', 'label' => 'Annulés'],
            ['val' => 'missed', 'label' => 'Non réalisés'],
        ];
        $currentStatus = request('status', '');
    @endphp
    <div class="flex flex-wrap gap-2" role="navigation" aria-label="Filtrer par statut">
        @foreach($statuses as $s)
            <a href="{{ route('doctor.appointments.index', $s['val'] ? ['status' => $s['val']] : []) }}"
               class="px-3 sm:px-4 py-2 rounded-xl text-sm font-semibold border transition-colors
                      {{ $currentStatus === $s['val']
                            ? 'bg-[#0d1c2f] text-white border-[#0d1c2f]'
                            : 'bg-white border-[#c2c6d4] text-[#526069] hover:border-[#003f87] hover:text-[#003f87]' }}"
               @if($currentStatus === $s['val']) aria-current="page" @endif>
                {{ $s['label'] }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse($appointments as $apt)
            @php
                $faded = in_array($apt->status, ['cancelled', 'rejected', 'missed'], true);
                $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
            @endphp

            <article class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm {{ $faded ? 'opacity-80' : '' }}
                            flex flex-col lg:flex-row lg:items-center gap-4 px-4 sm:px-5 py-4">
                <div class="w-14 h-14 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                    <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                    <span class="text-xl font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-[#0d1c2f] text-sm">{{ $apt->patient->name }}</p>
                        @include('doctor.partials.status-badge', ['appointment' => $apt])
                    </div>
                    <p class="text-xs text-[#526069] mt-1">
                        {{ $apt->appointment_date->format('d/m/Y') }}
                        ·
                        @if($apt->availability)
                            {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                        @else
                            {{ $apt->appointment_date->format('H:i') }}
                        @endif
                    </p>
                    @if($apt->notes)
                        <p class="text-xs text-[#526069] mt-0.5 truncate max-w-sm">{{ $apt->notes }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <a href="{{ route('doctor.appointments.show', $apt) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 border border-[#c2c6d4] hover:border-[#003f87] text-[#526069] hover:text-[#003f87] rounded-xl text-xs font-semibold">
                        Détails
                    </a>

                    @if($apt->status === 'pending')
                        @include('doctor.partials.pending-actions', ['appointment' => $apt])
                    @endif

                    @if($apt->status === 'accepted')
                        <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold">
                                Terminé
                            </button>
                        </form>
                        <form method="POST" action="{{ route('doctor.appointments.reschedule', $apt) }}" class="inline"
                              onsubmit="return confirm('Reporter {{ addslashes($apt->patient->name) }} ({{ $apt->appointment_date->format('d/m/Y H:i') }}) sur votre prochain créneau libre ?')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-semibold">
                                Reporter
                            </button>
                        </form>
                        <form method="POST" action="{{ route('doctor.appointments.destroy', $apt) }}" class="inline"
                              onsubmit="return confirm('Annuler le rendez-vous de {{ addslashes($apt->patient->name) }} ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-xs font-semibold">
                                Annuler
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-[#e0e7ff] py-16 text-center">
                <span class="material-symbols-outlined text-6xl text-[#c2c6d4] block mb-4" aria-hidden="true">calendar_today</span>
                @if(request('status'))
                    <p class="font-semibold text-[#0d1c2f]">Aucun rendez-vous pour ce filtre</p>
                    <p class="text-sm text-[#526069] mt-1 mb-4">Essayez un autre statut.</p>
                    <a href="{{ route('doctor.appointments.index') }}" class="text-sm font-semibold text-[#003f87] hover:underline">Voir tous les rendez-vous</a>
                @else
                    <p class="font-semibold text-[#0d1c2f]">Aucun rendez-vous pour le moment</p>
                    <p class="text-sm text-[#526069] mt-1 mb-4">Vos demandes apparaîtront ici dès qu’un patient réservera un créneau.</p>
                    <a href="{{ route('doctor.availabilities.create') }}" class="inline-flex items-center px-4 py-2 bg-[#003f87] text-white rounded-xl text-sm font-semibold">Créer une disponibilité</a>
                @endif
            </div>
        @endforelse
    </div>

    <div>{{ $appointments->links() }}</div>
</div>
@endsection
