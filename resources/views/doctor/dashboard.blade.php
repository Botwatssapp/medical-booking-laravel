@extends('layouts.doctor')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', now()->translatedFormat('l j F Y'))

@section('page-actions')
    @if(Auth::user()->hasDoctorProfile())
        <a href="{{ route('doctor.availabilities.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
            Ajouter des créneaux
        </a>
    @endif
@endsection

@section('doctor-content')
<div class="space-y-6">

    @if($profileIncomplete)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-10 text-center">
            <span class="material-symbols-outlined text-6xl text-amber-400 block mb-4" aria-hidden="true">pending</span>
            <h2 class="text-xl font-bold text-amber-800 mb-2">Profil en attente de validation</h2>
            <p class="text-amber-700 text-sm max-w-md mx-auto">
                Votre compte médecin a été créé. Un administrateur doit compléter votre profil
                médical avant que vous puissiez recevoir des rendez-vous.
            </p>
            <a href="{{ route('doctor.profile.edit') }}"
               class="inline-flex mt-4 text-sm font-semibold text-amber-900 underline">
                Compléter ma photo de compte
            </a>
        </div>
    @else

        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Demandes en attente', 'value' => $pendingAppointments, 'icon' => 'pending', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                ['label' => 'Confirmés', 'value' => $acceptedAppointments, 'icon' => 'check_circle', 'bg' => 'bg-green-100', 'text' => 'text-green-700'],
                ['label' => 'Terminés', 'value' => $completedAppointments, 'icon' => 'task_alt', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                ['label' => 'Créneaux libres', 'value' => $freeSlots, 'icon' => 'event_available', 'bg' => 'bg-[#eff4ff]', 'text' => 'text-[#003f87]'],
            ] as $stat)
                <div class="bg-white rounded-2xl border border-[#e0e7ff] p-5 flex items-center gap-4">
                    <div class="w-12 h-12 {{ $stat['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined {{ $stat['text'] }} text-2xl" aria-hidden="true">{{ $stat['icon'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-[#526069]">{{ $stat['label'] }}</p>
                        <p class="text-3xl font-bold text-[#0d1c2f]">{{ $stat['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-xs text-[#526069]">
            {{ $cancelledAppointments }} annulé(s) · {{ $rejectedAppointments }} refusé(s) · {{ $missedAppointments }} non réalisé(s) · {{ $totalAppointments }} au total
        </p>

        @if($nextAppointment)
            <div class="bg-[#003f87] text-white rounded-2xl p-6">
                <p class="text-xs font-bold uppercase tracking-wider opacity-70 mb-2">Prochain rendez-vous</p>
                <p class="text-2xl font-bold">{{ $nextAppointment->appointment_date->format('d/m/Y') }}</p>
                <p class="text-sm opacity-80 mt-1">
                    @if($nextAppointment->availability)
                        {{ substr($nextAppointment->availability->start_time, 0, 5) }} ·
                    @else
                        {{ $nextAppointment->appointment_date->format('H:i') }} ·
                    @endif
                    {{ $nextAppointment->patient->name }}
                </p>
                <a href="{{ route('doctor.appointments.show', $nextAppointment) }}"
                   class="inline-block mt-3 text-xs font-semibold underline underline-offset-2">
                    Voir le détail
                </a>
            </div>
        @endif

        @if($pendingAppointments > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="w-10 h-10 bg-yellow-400 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-yellow-900 text-xl" aria-hidden="true">notification_important</span>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-yellow-900">
                        {{ $pendingAppointments }} demande{{ $pendingAppointments > 1 ? 's' : '' }} en attente de réponse
                    </p>
                    <p class="text-sm text-yellow-800">Acceptez ou refusez pour libérer ou confirmer le créneau.</p>
                </div>
                <a href="{{ route('doctor.appointments.index', ['status' => 'pending']) }}"
                   class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 text-sm font-bold rounded-xl transition-colors shrink-0 text-center">
                    Voir les demandes
                </a>
            </div>
        @endif

        @if($freeSlots === 0)
            <div class="bg-white border border-[#e0e7ff] rounded-2xl px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1">
                    <p class="font-semibold text-[#0d1c2f]">Aucun créneau libre</p>
                    <p class="text-sm text-[#526069]">Ajoutez des disponibilités pour recevoir de nouvelles demandes.</p>
                </div>
                <a href="{{ route('doctor.availabilities.create') }}"
                   class="px-4 py-2 bg-[#003f87] text-white text-sm font-semibold rounded-xl text-center">
                    Créer une disponibilité
                </a>
            </div>
        @endif

        @if($todayAppointments->isNotEmpty())
            <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-[#e0e7ff]">
                    <h2 class="font-bold text-[#0d1c2f]">Aujourd’hui</h2>
                </div>
                <div class="divide-y divide-[#f0f4ff]">
                    @foreach($todayAppointments as $apt)
                        <div class="flex items-center gap-4 px-4 sm:px-6 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-[#0d1c2f]">{{ $apt->patient->name }}</p>
                                <p class="text-xs text-[#526069]">
                                    @if($apt->availability)
                                        {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                                    @else
                                        {{ $apt->appointment_date->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            @include('doctor.partials.status-badge', ['appointment' => $apt])
                            <a href="{{ route('doctor.appointments.show', $apt) }}" class="text-xs font-semibold text-[#003f87] hover:underline">Détail</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-[#e0e7ff] flex items-center justify-between">
                <h2 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">upcoming</span>
                    Prochains rendez-vous
                </h2>
                <a href="{{ route('doctor.appointments.index') }}"
                   class="text-xs font-semibold text-[#003f87] hover:underline">
                    Voir tous
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
                                <p class="font-semibold text-[#0d1c2f] text-sm">{{ $apt->patient->name }}</p>
                                @include('doctor.partials.status-badge', ['appointment' => $apt])
                            </div>
                            <p class="text-xs text-[#526069] mt-0.5">
                                @if($apt->availability)
                                    {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                                @else
                                    {{ $apt->appointment_date->format('H:i') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($apt->status === 'pending')
                                @include('doctor.partials.pending-actions', ['appointment' => $apt])
                            @endif
                            <a href="{{ route('doctor.appointments.show', $apt) }}"
                               class="px-3 py-2 border border-[#c2c6d4] rounded-lg text-xs font-semibold text-[#526069] hover:border-[#003f87] hover:text-[#003f87]"
                               aria-label="Voir le rendez-vous de {{ $apt->patient->name }}">
                                Détail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-[#c2c6d4] block mb-3" aria-hidden="true">calendar_today</span>
                        <p class="text-[#0d1c2f] font-medium">Aucun rendez-vous à venir.</p>
                        <p class="text-sm text-[#526069] mt-1">Ajoutez des créneaux pour recevoir des demandes.</p>
                        <a href="{{ route('doctor.availabilities.create') }}"
                           class="inline-flex mt-3 text-sm font-semibold text-[#003f87] hover:underline">
                            Créer une disponibilité
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

    @endif
</div>
@endsection
