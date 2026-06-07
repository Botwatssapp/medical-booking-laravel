@extends('layouts.app')
@section('title', 'Mon espace santé')

@section('content')
<div class="max-w-[1200px] mx-auto px-8 py-10">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Bonjour, {{ Auth::user()->name }} 👋</h1>
            <p class="text-[#526069] mt-1">{{ now()->isoFormat('dddd D MMMM Y') }}</p>
        </div>
        <a href="{{ route('patient.doctors.index') }}"
           class="flex items-center gap-2 px-5 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Prendre un RDV
        </a>
    </div>

    <div class="grid grid-cols-12 gap-6">

        {{-- ── Colonne gauche : profil + stats ── --}}
        <div class="col-span-12 lg:col-span-4 space-y-5">

            {{-- Carte profil --}}
            <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-[#003f87]/10 mb-3">
                        @if(Auth::user()->profile_image_url)
                            <img src="{{ Auth::user()->profile_image_url }}"
                                 alt="{{ Auth::user()->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#003f87] flex items-center justify-center text-white text-2xl font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <p class="font-bold text-[#0d1c2f] text-lg">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-[#526069]">{{ Auth::user()->email }}</p>
                    <a href="{{ route('patient.profile.edit') }}"
                       class="mt-3 text-xs font-semibold text-[#003f87] hover:underline">
                        Modifier le profil →
                    </a>
                </div>

                {{-- Stats --}}
                <div class="space-y-3 border-t border-[#e0e7ff] pt-4">
                    @foreach([
                        ['label' => 'Total rendez-vous', 'value' => $totalAppointments,     'color' => 'text-[#0d1c2f]'],
                        ['label' => 'Confirmés',         'value' => $confirmedAppointments,  'color' => 'text-green-600'],
                        ['label' => 'En attente',        'value' => $pendingAppointments,    'color' => 'text-yellow-600'],
                        ['label' => 'Annulés',           'value' => $cancelledAppointments,  'color' => 'text-red-500'],
                    ] as $stat)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#526069]">{{ $stat['label'] }}</span>
                            <span class="font-bold text-sm {{ $stat['color'] }}">{{ $stat['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Prochain RDV --}}
            <div class="bg-[#003f87] text-white rounded-2xl p-6 relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-70 mb-2">Prochain rendez-vous</p>
                    @if($upcomingAppointments->isNotEmpty())
                        @php $next = $upcomingAppointments->first(); @endphp
                        <p class="text-2xl font-bold">{{ $next->appointment_date->format('d/m/Y') }}</p>
                        <p class="text-sm opacity-80 mt-1">
                            @if($next->availability)
                                {{ substr($next->availability->start_time, 0, 5) }} ·
                            @endif
                            Dr. {{ $next->doctor->user->name }}
                        </p>
                        <span class="inline-block mt-2 px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-semibold">
                            {{ $next->doctor->speciality->name }}
                        </span>
                    @else
                        <p class="text-xl font-bold">Aucun RDV à venir</p>
                        <a href="{{ route('patient.doctors.index') }}"
                           class="inline-block mt-2 text-xs font-semibold opacity-80 hover:opacity-100 underline underline-offset-2">
                            Prendre rendez-vous →
                        </a>
                    @endif
                </div>
                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-[120px] opacity-10">medical_services</span>
            </div>

            {{-- Liens rapides --}}
            <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-5 space-y-2">
                <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-3">Navigation rapide</p>
                <a href="{{ route('patient.appointments.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#f8faff] transition-colors group">
                    <span class="material-symbols-outlined text-[#003f87] text-[20px]">calendar_month</span>
                    <span class="text-sm font-medium text-[#0d1c2f] group-hover:text-[#003f87]">Mes rendez-vous</span>
                    <span class="material-symbols-outlined text-[#c2c6d4] text-[16px] ml-auto">chevron_right</span>
                </a>
                <a href="{{ route('patient.doctors.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#f8faff] transition-colors group">
                    <span class="material-symbols-outlined text-[#003f87] text-[20px]">stethoscope</span>
                    <span class="text-sm font-medium text-[#0d1c2f] group-hover:text-[#003f87]">Trouver un médecin</span>
                    <span class="material-symbols-outlined text-[#c2c6d4] text-[16px] ml-auto">chevron_right</span>
                </a>
                <a href="{{ route('patient.notifications.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#f8faff] transition-colors group">
                    <span class="material-symbols-outlined text-[#003f87] text-[20px]">notifications</span>
                    <span class="text-sm font-medium text-[#0d1c2f] group-hover:text-[#003f87]">Notifications</span>
                    @php $unread = Auth::user()->unreadNotifications()->count(); @endphp
                    @if($unread > 0)
                        <span class="ml-auto min-w-[20px] h-5 px-1 bg-red-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center">
                            {{ $unread }}
                        </span>
                    @else
                        <span class="material-symbols-outlined text-[#c2c6d4] text-[16px] ml-auto">chevron_right</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- ── Colonne droite : rendez-vous ── --}}
        <div class="col-span-12 lg:col-span-8 space-y-5">

            {{-- Prochains RDV confirmés --}}
            <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#e0e7ff] flex items-center justify-between">
                    <h2 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#003f87]">upcoming</span>
                        Prochains rendez-vous
                    </h2>
                    <a href="{{ route('patient.appointments.index') }}"
                       class="text-xs font-semibold text-[#003f87] hover:underline">
                        Voir tout →
                    </a>
                </div>

                <div class="divide-y divide-[#f0f4ff]">
                    @forelse($upcomingAppointments as $apt)
                        @php
                            $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4 hover:bg-[#f8faff] transition-colors">
                            <div class="w-14 h-14 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                                <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                                <span class="text-xl font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-[#0d1c2f] text-sm">Dr. {{ $apt->doctor->user->name }}</p>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-800">Confirmé</span>
                                </div>
                                <p class="text-xs text-[#526069] mt-0.5">
                                    {{ $apt->doctor->speciality->name }}
                                    @if($apt->availability)
                                        · {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('patient.doctors.show', $apt->doctor) }}"
                               class="text-xs font-semibold text-[#003f87] hover:underline shrink-0">
                                Voir →
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center">
                            <span class="material-symbols-outlined text-4xl text-[#c2c6d4] block mb-2">event_available</span>
                            <p class="text-sm text-[#526069]">Aucun rendez-vous confirmé à venir.</p>
                            <a href="{{ route('patient.doctors.index') }}"
                               class="inline-block mt-2 text-sm font-semibold text-[#003f87] hover:underline">
                                Prendre un rendez-vous →
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- En attente de confirmation --}}
            @if($pendingAppointments > 0)
                @php
                    $pendingList = auth()->user()->appointments()->pending()->with(['doctor.user','doctor.speciality','availability'])->orderBy('appointment_date')->limit(3)->get();
                @endphp
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-yellow-200 flex items-center gap-3">
                        <span class="material-symbols-outlined text-yellow-600">pending</span>
                        <h2 class="font-bold text-yellow-900">{{ $pendingAppointments }} demande{{ $pendingAppointments > 1 ? 's' : '' }} en attente de confirmation</h2>
                    </div>
                    <div class="divide-y divide-yellow-100">
                        @foreach($pendingList as $apt)
                            @php $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']; @endphp
                            <div class="flex items-center gap-4 px-6 py-3">
                                <div class="w-12 h-12 bg-white/60 rounded-xl flex flex-col items-center justify-center text-yellow-700 shrink-0">
                                    <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                                    <span class="text-lg font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-yellow-900">Dr. {{ $apt->doctor->user->name }}</p>
                                    <p class="text-xs text-yellow-700">{{ $apt->doctor->speciality->name }}</p>
                                </div>
                                <form method="POST" action="{{ route('patient.appointments.destroy', $apt) }}" class="inline"
                                      onsubmit="return confirm('Annuler cette demande ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-semibold text-red-600 hover:underline">
                                        Annuler
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Historique récent --}}
            @if($pastAppointments->isNotEmpty())
                <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#e0e7ff]">
                        <h2 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#003f87]">history</span>
                            Consultations récentes
                        </h2>
                    </div>
                    <div class="divide-y divide-[#f0f4ff]">
                        @foreach($pastAppointments as $apt)
                            @php $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']; @endphp
                            <div class="flex items-center gap-4 px-6 py-3 opacity-80">
                                <div class="w-12 h-12 bg-[#f0f4ff] rounded-xl flex flex-col items-center justify-center text-[#526069] shrink-0">
                                    <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                                    <span class="text-lg font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-[#0d1c2f]">Dr. {{ $apt->doctor->user->name }}</p>
                                    <p class="text-xs text-[#526069]">{{ $apt->doctor->speciality->name }}</p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">Terminé</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
