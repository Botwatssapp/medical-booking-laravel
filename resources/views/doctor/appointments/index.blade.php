@extends('layouts.doctor')
@section('page-title', 'Mes rendez-vous')
@section('page-subtitle', 'Gérez et suivez les demandes de vos patients')

@section('doctor-content')
<div class="space-y-5">

    {{-- ── Filtres rapides par statut ── --}}
    @php
        $statuses = [
            ['val' => '',          'label' => 'Tous',        'color' => 'border-[#003f87] text-[#003f87]'],
            ['val' => 'pending',   'label' => 'En attente',  'color' => 'border-yellow-400 text-yellow-700'],
            ['val' => 'accepted',  'label' => 'Confirmés',   'color' => 'border-green-500 text-green-700'],
            ['val' => 'completed', 'label' => 'Terminés',    'color' => 'border-blue-500 text-blue-700'],
            ['val' => 'cancelled', 'label' => 'Annulés',     'color' => 'border-gray-400 text-gray-600'],
        ];
        $currentStatus = request('status', '');
    @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($statuses as $s)
            <a href="{{ route('doctor.appointments.index', $s['val'] ? ['status' => $s['val']] : []) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold border transition-colors
                      {{ $currentStatus === $s['val']
                            ? 'bg-[#0d1c2f] text-white border-[#0d1c2f]'
                            : 'bg-white border-[#c2c6d4] text-[#526069] hover:border-[#003f87] hover:text-[#003f87]' }}">
                {{ $s['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ── Liste ── --}}
    <div class="space-y-3">
        @forelse($appointments as $apt)
            @php
                $badge = match($apt->status) {
                    'pending'   => 'bg-yellow-100 text-yellow-800',
                    'accepted'  => 'bg-green-100 text-green-800',
                    'rejected'  => 'bg-red-100 text-red-800',
                    'cancelled' => 'bg-gray-100 text-gray-600',
                    'completed' => 'bg-blue-100 text-blue-800',
                    'missed'    => 'bg-orange-100 text-orange-800',
                    default     => 'bg-gray-100 text-gray-600',
                };
                $statusLabel = match($apt->status) {
                    'pending'   => 'En attente',
                    'accepted'  => 'Confirmé',
                    'rejected'  => 'Refusé',
                    'cancelled' => 'Annulé',
                    'completed' => 'Terminé',
                    'missed'    => 'Manqué',
                    default     => ucfirst($apt->status),
                };
                $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                $faded = in_array($apt->status, ['cancelled','rejected','missed']);
            @endphp

            <div class="bg-white rounded-2xl border {{ $faded ? 'border-[#e0e7ff] opacity-70' : 'border-[#e0e7ff]' }} shadow-sm
                        flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4 hover:border-[#003f87]/30 transition-colors">

                {{-- Date badge --}}
                <div class="w-14 h-14 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                    <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                    <span class="text-xl font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                </div>

                {{-- Infos patient --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="w-7 h-7 bg-[#eff4ff] rounded-full flex items-center justify-center text-[#003f87] font-bold text-xs shrink-0">
                            {{ strtoupper(substr($apt->patient->name, 0, 1)) }}
                        </div>
                        <p class="font-semibold text-[#0d1c2f] text-sm">{{ $apt->patient->name }}</p>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badge }}">{{ $statusLabel }}</span>
                    </div>
                    <p class="text-xs text-[#526069] mt-1">
                        {{ $apt->appointment_date->format('d/m/Y') }}
                        @if($apt->availability)
                            &nbsp;·&nbsp;
                            {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                        @endif
                    </p>
                    @if($apt->notes)
                        <p class="text-xs text-[#526069] mt-0.5 truncate max-w-sm">{{ $apt->notes }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <a href="{{ route('doctor.appointments.show', $apt) }}"
                       class="flex items-center gap-1.5 px-3 py-2 border border-[#c2c6d4] hover:border-[#003f87] text-[#526069] hover:text-[#003f87] rounded-xl text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                        Détails
                    </a>

                    @if($apt->status === 'pending')
                        <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                Accepter
                            </button>
                        </form>
                        <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[14px]">cancel</span>
                                Refuser
                            </button>
                        </form>
                    @endif

                    @if($apt->status === 'accepted')
                        <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[14px]">task_alt</span>
                                Terminé
                            </button>
                        </form>
                        <form method="POST" action="{{ route('doctor.appointments.reschedule', $apt) }}" class="inline"
                              onsubmit="return confirm('Reporter {{ addslashes($apt->patient->name) }} sur votre prochain créneau disponible ?')">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[14px]">event_repeat</span>
                                Reporter
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-[#e0e7ff] py-16 text-center">
                <span class="material-symbols-outlined text-6xl text-[#c2c6d4] block mb-4">calendar_today</span>
                <p class="font-semibold text-[#0d1c2f]">Aucun rendez-vous trouvé</p>
                <p class="text-sm text-[#526069] mt-1">
                    @if(request('status'))
                        Aucun rendez-vous avec le statut sélectionné.
                    @else
                        Vos rendez-vous apparaîtront ici.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <div>{{ $appointments->links() }}</div>
</div>
@endsection
