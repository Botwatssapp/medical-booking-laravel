@extends('layouts.doctor')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', now()->format('l d F Y'))

@section('page-actions')
    <a href="{{ route('doctor.availabilities.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Ajouter des créneaux
    </a>
@endsection

@section('doctor-content')
<div class="space-y-6">

    {{-- Profil non encore configuré --}}
    @if($profileIncomplete)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-10 text-center">
            <span class="material-symbols-outlined text-6xl text-amber-400 block mb-4">pending</span>
            <h2 class="text-xl font-bold text-amber-800 mb-2">Profil en attente de validation</h2>
            <p class="text-amber-700 text-sm max-w-md mx-auto">
                Votre compte médecin a été créé. Un administrateur doit compléter votre profil
                médical avant que vous puissiez recevoir des rendez-vous.
            </p>
        </div>
    @else

        {{-- ── Stat cards ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Total RDV',  'value' => $totalAppointments,    'icon' => 'calendar_month', 'bg' => 'bg-blue-100',   'text' => 'text-blue-600'],
                ['label' => 'En attente', 'value' => $pendingAppointments,  'icon' => 'pending',         'bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
                ['label' => 'Acceptés',   'value' => $acceptedAppointments, 'icon' => 'check_circle',    'bg' => 'bg-green-100',  'text' => 'text-green-600'],
                ['label' => 'Refusés',    'value' => $rejectedAppointments, 'icon' => 'cancel',          'bg' => 'bg-red-100',    'text' => 'text-red-500'],
            ] as $stat)
                <div class="bg-white rounded-2xl border border-[#e0e7ff] p-5 flex items-center gap-4">
                    <div class="w-12 h-12 {{ $stat['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined {{ $stat['text'] }} text-2xl">{{ $stat['icon'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-[#526069]">{{ $stat['label'] }}</p>
                        <p class="text-3xl font-bold text-[#0d1c2f]">{{ $stat['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Alerte rendez-vous en attente ── --}}
        @if($pendingAppointments > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl px-5 py-4 flex items-center gap-4">
                <div class="w-10 h-10 bg-yellow-400 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-xl">notification_important</span>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-yellow-900">
                        {{ $pendingAppointments }} demande{{ $pendingAppointments > 1 ? 's' : '' }} en attente de réponse
                    </p>
                    <p class="text-sm text-yellow-700">Répondez rapidement pour confirmer les créneaux à vos patients.</p>
                </div>
                <a href="{{ route('doctor.appointments.index') }}?status=pending"
                   class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 text-sm font-bold rounded-xl transition-colors shrink-0">
                    Voir les demandes
                </a>
            </div>
        @endif

        {{-- ── Prochains rendez-vous ── --}}
        <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#e0e7ff] flex items-center justify-between">
                <h2 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#003f87]">upcoming</span>
                    Prochains rendez-vous
                </h2>
                <a href="{{ route('doctor.appointments.index') }}"
                   class="text-xs font-semibold text-[#003f87] hover:underline">
                    Voir tous →
                </a>
            </div>

            <div class="divide-y divide-[#f0f4ff]">
                @forelse($upcomingAppointments as $apt)
                    @php
                        $badge = match($apt->status) {
                            'accepted'  => 'bg-green-100 text-green-800',
                            'pending'   => 'bg-yellow-100 text-yellow-800',
                            'cancelled' => 'bg-gray-100 text-gray-600',
                            'rejected'  => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                        $statusLabel = match($apt->status) {
                            'accepted'  => 'Confirmé',
                            'pending'   => 'En attente',
                            'cancelled' => 'Annulé',
                            'rejected'  => 'Refusé',
                            default     => ucfirst($apt->status),
                        };
                    @endphp
                    <div class="flex items-center gap-4 px-6 py-4 hover:bg-[#f8faff] transition-colors">

                        {{-- Date badge --}}
                        <div class="w-14 h-14 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                            @php
                                $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                            @endphp
                            <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                            <span class="text-xl font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                        </div>

                        {{-- Infos --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-semibold text-[#0d1c2f] text-sm">{{ $apt->patient->name }}</p>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $badge }}">{{ $statusLabel }}</span>
                            </div>
                            @if($apt->availability)
                                <p class="text-xs text-[#526069] mt-0.5">
                                    {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                                </p>
                            @endif
                            @if($apt->notes)
                                <p class="text-xs text-[#526069] mt-0.5 truncate">{{ $apt->notes }}</p>
                            @endif
                        </div>

                        {{-- Actions rapides --}}
                        <div class="flex items-center gap-2 shrink-0">
                            @if($apt->status === 'pending')
                                <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit"
                                        class="w-8 h-8 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center justify-center transition-colors"
                                        title="Accepter">
                                        <span class="material-symbols-outlined text-[16px]">check</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit"
                                        class="w-8 h-8 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg flex items-center justify-center transition-colors"
                                        title="Refuser">
                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('doctor.appointments.show', $apt) }}"
                               class="w-8 h-8 border border-[#c2c6d4] hover:border-[#003f87] text-[#526069] hover:text-[#003f87] rounded-lg flex items-center justify-center transition-colors"
                               title="Détails">
                                <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-[#c2c6d4] block mb-3">calendar_today</span>
                        <p class="text-[#526069] font-medium">Aucun rendez-vous à venir.</p>
                        <p class="text-xs text-[#526069] mt-1">Ajoutez des créneaux pour recevoir des demandes.</p>
                    </div>
                @endforelse
            </div>
        </div>

    @endif
</div>
@endsection
