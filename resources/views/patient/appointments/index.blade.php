@extends('layouts.app')
@section('title', 'Mes rendez-vous')

@section('content')
<div class="max-w-[1100px] mx-auto px-8 py-10">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Mes rendez-vous</h1>
            <p class="text-[#526069] mt-1">Historique et gestion de vos consultations.</p>
        </div>
        <a href="{{ route('patient.doctors.index') }}"
           class="flex items-center gap-2 px-5 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Nouveau rendez-vous
        </a>
    </div>

    {{-- Filtres rapides --}}
    @php
        $statusFilters = [
            ['val' => '',          'label' => 'Tous'],
            ['val' => 'accepted',  'label' => 'Confirmés'],
            ['val' => 'pending',   'label' => 'En attente'],
            ['val' => 'completed', 'label' => 'Terminés'],
            ['val' => 'cancelled', 'label' => 'Annulés'],
        ];
        $currentStatus = request('status', '');
    @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($statusFilters as $f)
            <a href="{{ route('patient.appointments.index', $f['val'] ? ['status' => $f['val']] : []) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold border transition-colors
                      {{ $currentStatus === $f['val']
                            ? 'bg-[#0d1c2f] text-white border-[#0d1c2f]'
                            : 'bg-white border-[#c2c6d4] text-[#526069] hover:border-[#003f87] hover:text-[#003f87]' }}">
                {{ $f['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Liste des rendez-vous --}}
    <div class="space-y-3">
        @forelse($appointments as $appointment)
            @php
                $status = $appointment->status;
                $badge = match($status) {
                    'pending'   => 'bg-yellow-100 text-yellow-800',
                    'accepted'  => 'bg-green-100 text-green-800',
                    'rejected'  => 'bg-red-100 text-red-800',
                    'cancelled' => 'bg-gray-100 text-gray-600',
                    'completed' => 'bg-blue-100 text-blue-800',
                    'missed'    => 'bg-orange-100 text-orange-800',
                    default     => 'bg-gray-100 text-gray-600',
                };
                $statusLabel = match($status) {
                    'pending'   => 'En attente',
                    'accepted'  => 'Confirmé',
                    'rejected'  => 'Refusé',
                    'cancelled' => 'Annulé',
                    'completed' => 'Terminé',
                    'missed'    => 'Manqué',
                    default     => ucfirst($status),
                };
                $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                $faded = in_array($status, ['cancelled','rejected','missed']);
            @endphp

            <div class="bg-white rounded-2xl border {{ $faded ? 'border-[#e0e7ff] opacity-70' : 'border-[#e0e7ff]' }} shadow-sm
                        flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4">

                {{-- Date badge --}}
                <div class="w-14 h-14 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                    <span class="text-[10px] font-bold uppercase">{{ $mois[$appointment->appointment_date->month - 1] }}</span>
                    <span class="text-xl font-bold leading-tight">{{ $appointment->appointment_date->format('d') }}</span>
                </div>

                {{-- Infos médecin --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-xs shrink-0">
                            {{ strtoupper(substr($appointment->doctor->user->name, 0, 1)) }}
                        </div>
                        <p class="font-semibold text-[#0d1c2f] text-sm">Dr. {{ $appointment->doctor->user->name }}</p>
                        <span class="text-xs text-[#526069]">·</span>
                        <p class="text-xs text-[#526069]">{{ $appointment->doctor->speciality->name }}</p>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badge }}">{{ $statusLabel }}</span>
                    </div>
                    <p class="text-xs text-[#526069] mt-1">
                        {{ $appointment->appointment_date->format('d/m/Y') }}
                        @if($appointment->availability)
                            &nbsp;·&nbsp;
                            {{ substr($appointment->availability->start_time, 0, 5) }} – {{ substr($appointment->availability->end_time, 0, 5) }}
                        @endif
                    </p>
                    @if($appointment->notes)
                        <p class="text-xs text-[#526069] mt-0.5 truncate max-w-md">{{ $appointment->notes }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if($status === 'pending')
                        <form method="POST" action="{{ route('patient.appointments.destroy', $appointment) }}" class="inline"
                              onsubmit="return confirm('Annuler ce rendez-vous ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[14px]">cancel</span>
                                Annuler
                            </button>
                        </form>
                    @endif
                    @if($status === 'accepted' && $appointment->appointment_date > now())
                        <a href="{{ route('patient.doctors.show', $appointment->doctor) }}"
                           class="flex items-center gap-1.5 px-3 py-2 border border-[#c2c6d4] hover:border-[#003f87] text-[#526069] hover:text-[#003f87] rounded-xl text-xs font-semibold transition-colors">
                            <span class="material-symbols-outlined text-[14px]">event</span>
                            Voir médecin
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-[#e0e7ff] py-16 text-center shadow-sm">
                <span class="material-symbols-outlined text-6xl text-[#c2c6d4] block mb-4">calendar_today</span>
                <p class="font-semibold text-[#0d1c2f] mb-1">Aucun rendez-vous trouvé</p>
                <p class="text-sm text-[#526069] mb-6">Prenez votre premier rendez-vous avec un médecin.</p>
                <a href="{{ route('patient.doctors.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Trouver un médecin
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $appointments->links() }}</div>
</div>
@endsection
