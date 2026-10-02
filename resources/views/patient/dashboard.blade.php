@extends('layouts.app')
@section('title', 'Mon espace santé')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-8 py-8 sm:py-10">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#0d1c2f]">Bonjour, {{ Auth::user()->name }}</h1>
            <p class="text-[#526069] mt-1">{{ now()->isoFormat('dddd D MMMM Y') }}</p>
        </div>
        <a href="{{ route('patient.doctors.index') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
            Prendre un rendez-vous
        </a>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 space-y-5">
            <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-[#003f87]/10 mb-3">
                        @if(Auth::user()->profile_image_url)
                            <img src="{{ Auth::user()->profile_image_url }}"
                                 alt="Photo de profil de {{ Auth::user()->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-[#003f87] flex items-center justify-center text-white text-2xl font-bold" aria-hidden="true">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <p class="font-bold text-[#0d1c2f] text-lg">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-[#526069]">{{ Auth::user()->email }}</p>
                    <a href="{{ route('patient.profile.edit') }}"
                       class="mt-3 text-xs font-semibold text-[#003f87] hover:underline">
                        Modifier le profil
                    </a>
                </div>

                <div class="space-y-3 border-t border-[#e0e7ff] pt-4">
                    @foreach([
                        ['label' => 'Total rendez-vous', 'value' => $totalAppointments, 'color' => 'text-[#0d1c2f]'],
                        ['label' => 'En attente', 'value' => $pendingAppointments, 'color' => 'text-yellow-700'],
                        ['label' => 'Confirmés', 'value' => $confirmedAppointments, 'color' => 'text-green-700'],
                        ['label' => 'Terminés', 'value' => $completedAppointments, 'color' => 'text-blue-700'],
                        ['label' => 'Annulés / refusés', 'value' => $cancelledAppointments, 'color' => 'text-red-600'],
                    ] as $stat)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#526069]">{{ $stat['label'] }}</span>
                            <span class="font-bold text-sm {{ $stat['color'] }}">{{ $stat['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-[#003f87] text-white rounded-2xl p-6 relative overflow-hidden shadow-sm">
                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-70 mb-2">Prochain rendez-vous</p>
                    @if($upcomingAppointments->isNotEmpty())
                        @php $next = $upcomingAppointments->first(); @endphp
                        <p class="text-2xl font-bold">{{ $next->appointment_date->format('d/m/Y') }}</p>
                        <p class="text-sm opacity-80 mt-1">
                            @if($next->availability)
                                {{ substr($next->availability->start_time, 0, 5) }} ·
                            @else
                                {{ $next->appointment_date->format('H:i') }} ·
                            @endif
                            Dr. {{ $next->doctor->user->name }}
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-block px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-semibold">
                                {{ $next->doctor->speciality->name }}
                            </span>
                            @include('patient.partials.status-badge', ['appointment' => $next])
                        </div>
                        <a href="{{ route('patient.appointments.show', $next) }}"
                           class="inline-block mt-3 text-xs font-semibold underline underline-offset-2">
                            Voir le détail
                        </a>
                    @else
                        <p class="text-xl font-bold">Aucun rendez-vous à venir</p>
                        <p class="text-sm opacity-80 mt-1">Trouvez un médecin et réservez un créneau.</p>
                        <a href="{{ route('patient.doctors.index') }}"
                           class="inline-block mt-3 text-xs font-semibold underline underline-offset-2">
                            Trouver un médecin
                        </a>
                    @endif
                </div>
                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-[120px] opacity-10" aria-hidden="true">medical_services</span>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 space-y-5">
            <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-[#e0e7ff] flex items-center justify-between">
                    <h2 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">upcoming</span>
                        Prochains rendez-vous
                    </h2>
                    <a href="{{ route('patient.appointments.index') }}"
                       class="text-xs font-semibold text-[#003f87] hover:underline">
                        Voir tout
                    </a>
                </div>

                <div class="divide-y divide-[#f0f4ff]">
                    @forelse($upcomingAppointments as $apt)
                        @php $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']; @endphp
                        <div class="flex items-center gap-4 px-4 sm:px-6 py-4">
                            <div class="w-14 h-14 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                                <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                                <span class="text-xl font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-[#0d1c2f] text-sm">Dr. {{ $apt->doctor->user->name }}</p>
                                    @include('patient.partials.status-badge', ['appointment' => $apt])
                                </div>
                                <p class="text-xs text-[#526069] mt-0.5">
                                    {{ $apt->doctor->speciality->name }}
                                    ·
                                    @if($apt->availability)
                                        {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                                    @else
                                        {{ $apt->appointment_date->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('patient.appointments.show', $apt) }}"
                               class="text-xs font-semibold text-[#003f87] hover:underline shrink-0">
                                Détail
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center">
                            <span class="material-symbols-outlined text-4xl text-[#c2c6d4] block mb-2" aria-hidden="true">event_available</span>
                            <p class="text-sm font-medium text-[#0d1c2f]">Aucun rendez-vous à venir.</p>
                            <p class="text-sm text-[#526069] mt-1">Réservez un créneau pour consulter un médecin.</p>
                            <a href="{{ route('patient.doctors.index') }}"
                               class="inline-block mt-3 text-sm font-semibold text-[#003f87] hover:underline">
                                Trouver un médecin
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($pendingList->isNotEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-yellow-200 flex items-center gap-3">
                        <span class="material-symbols-outlined text-yellow-700" aria-hidden="true">pending</span>
                        <h2 class="font-bold text-yellow-900">{{ $pendingAppointments }} demande{{ $pendingAppointments > 1 ? 's' : '' }} en attente</h2>
                    </div>
                    <div class="divide-y divide-yellow-100">
                        @foreach($pendingList as $apt)
                            @php $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']; @endphp
                            <div class="flex items-center gap-4 px-4 sm:px-6 py-3">
                                <div class="w-12 h-12 bg-white/60 rounded-xl flex flex-col items-center justify-center text-yellow-700 shrink-0">
                                    <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                                    <span class="text-lg font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-yellow-900">Dr. {{ $apt->doctor->user->name }}</p>
                                    <p class="text-xs text-yellow-800">{{ $apt->doctor->speciality->name }}</p>
                                </div>
                                <a href="{{ route('patient.appointments.show', $apt) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Détail
                                </a>
                                @include('patient.partials.cancel-form', ['appointment' => $apt])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($pastAppointments->isNotEmpty())
                <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-[#e0e7ff]">
                        <h2 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">history</span>
                            Consultations récentes
                        </h2>
                    </div>
                    <div class="divide-y divide-[#f0f4ff]">
                        @foreach($pastAppointments as $apt)
                            @php $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']; @endphp
                            <div class="flex items-center gap-4 px-4 sm:px-6 py-3">
                                <div class="w-12 h-12 bg-[#f0f4ff] rounded-xl flex flex-col items-center justify-center text-[#526069] shrink-0">
                                    <span class="text-[10px] font-bold uppercase">{{ $mois[$apt->appointment_date->month - 1] }}</span>
                                    <span class="text-lg font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-[#0d1c2f]">Dr. {{ $apt->doctor->user->name }}</p>
                                    <p class="text-xs text-[#526069]">{{ $apt->doctor->speciality->name }}</p>
                                </div>
                                @include('patient.partials.status-badge', ['appointment' => $apt])
                                <a href="{{ route('patient.appointments.show', $apt) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Détail
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
