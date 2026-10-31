@php
    $hour = now()->hour;
    $hello = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $next = $upcomingAppointments->first();
    $when = function ($appointment) {
        $start = $appointment->availability
            ? $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->start_time)
            : $appointment->appointment_date;
        $end = $appointment->availability
            ? $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->end_time)
            : $appointment->appointment_date->copy()->addHour();

        return compact('start', 'end');
    };
    $icsFor = function ($appointment) use ($when) {
        $times = $when($appointment);

        return [
            'id' => $appointment->id,
            'stamp' => now()->format('Ymd\THis'),
            'start' => $times['start']->format('Ymd\THis'),
            'end' => $times['end']->format('Ymd\THis'),
            'summary' => 'Rendez-vous Dr. '.$appointment->doctor->user->name,
            'description' => 'SantéConnect #'.$appointment->id.' — '.$appointment->doctor->speciality->name,
            'location' => $appointment->doctor->address ?? '',
        ];
    };
@endphp
@extends('layouts.app')
@section('title', 'Mon espace santé')

@section('content')
<div class="bg-[#faf8ff] text-[#131b2e] min-h-screen" x-data="{ tab: 'upcoming' }">
    <div class="sc-page sc-stack">
        <section class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 lg:p-8 rounded-xl shadow-sm relative overflow-hidden">
            <div class="space-y-1 z-10">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] uppercase tracking-wider text-[#004ac6] font-bold">Espace patient</span>
                    <span class="text-[#c3c6d7] text-[13px]">•</span>
                    <span class="text-[11px] text-[#006242] font-semibold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#006242]"></span> Profil connecté
                    </span>
                </div>
                <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight">{{ $hello }}, {{ Auth::user()->name }}</h1>
                <p class="text-[15px] leading-6 text-[#434655] max-w-2xl">
                    Gérez vos rendez-vous à venir. Aucune ordonnance, téléconsultation ou dossier médical n’est géré ici.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 z-10 shrink-0">
                <a href="{{ route('patient.doctors.index') }}" class="flex items-center gap-1.5 px-4 h-11 bg-[#004ac6] hover:bg-[#003ea8] text-white rounded-lg text-[14px] font-semibold shadow-md">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
                    Prendre un rendez-vous
                </a>
                <button type="button" class="flex items-center gap-1.5 px-4 h-11 bg-[#e2e7ff] text-[#004ac6] hover:bg-[#dae2fd] rounded-lg text-[14px] font-semibold"
                        @click="Alpine.$data(document.body).showToast('La téléconsultation n’est pas gérée.')">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">videocam</span>
                    Salle vidéo
                </button>
                <a href="{{ route('patient.profile.edit') }}" class="flex items-center gap-1.5 px-4 h-11 bg-[#f2f3ff] text-[#434655] hover:text-[#131b2e] rounded-lg text-[14px] font-semibold">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">folder_shared</span>
                    Mon profil
                </a>
            </div>
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-[#dae2fd]/40 rounded-full blur-3xl pointer-events-none"></div>
        </section>

        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach([
                ['value' => $upcomingCount, 'label' => 'À venir', 'icon' => 'event_upcoming', 'tone' => 'bg-[#dae2fd] text-[#004ac6]'],
                ['value' => $completedAppointments, 'label' => 'Terminés', 'icon' => 'check_circle', 'tone' => 'bg-[#6ffbbe] text-[#005236]'],
                ['value' => $pendingAppointments, 'label' => 'En attente', 'icon' => 'hourglass_top', 'tone' => 'bg-[#89f5e7] text-[#005049]'],
                ['value' => $totalAppointments, 'label' => 'Total', 'icon' => 'description', 'tone' => 'bg-[#e2e7ff] text-[#004ac6]'],
            ] as $stat)
                <div class="p-4 sm:p-6 bg-white rounded-xl shadow-sm flex items-center gap-3 sm:gap-4 h-full min-w-0">
                    <div class="w-12 h-12 rounded-xl {{ $stat['tone'] }} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">{{ $stat['icon'] }}</span>
                    </div>
                    <div class="min-w-0">
                        <span class="font-display text-[28px] leading-9 font-bold block">{{ $stat['value'] }}</span>
                        <span class="text-[11px] text-[#434655] block mt-1 leading-4">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @endforeach
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-8 space-y-8">
                @if($next)
                    @php
                        $times = $when($next);
                        $photo = $next->doctor->user->profile_image_url ?? ($next->doctor->photo ? asset('storage/'.$next->doctor->photo) : null);
                        $maps = $next->doctor->address
                            ? 'https://www.google.com/maps/search/?'.http_build_query(['api' => 1, 'query' => $next->doctor->address])
                            : null;
                        $duration = $times['start']->diffInMinutes($times['end']);
                        $countdown = $times['start']->isFuture()
                            ? ($times['start']->diffInHours(now()) >= 24
                                ? 'Dans '.$times['start']->diffInDays(now()).' j'
                                : 'Dans '.$times['start']->diffInHours(now()).' h '.$times['start']->diff(now())->i.' min')
                            : 'Horaire passé';
                    @endphp
                    <article class="relative bg-white rounded-xl shadow-md p-6 lg:p-8 overflow-hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#dae2fd] text-[#004ac6] text-[11px] font-semibold">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">verified</span>
                                    {{ $next->status === 'accepted' ? 'Prochaine séance confirmée' : 'Prochaine demande' }}
                                </span>
                                <span class="text-[12px] font-semibold text-[#434655]">Rendez-vous #{{ $next->id }}</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#dbe1ff] text-[#00174b] text-[12px] font-semibold self-start sm:self-auto">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#004ac6] animate-ping inline-block"></span>
                                <span>{{ $countdown }}</span>
                            </div>
                        </div>
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <div class="md:col-span-7 flex items-start gap-4">
                                <div class="relative shrink-0">
                                    <div class="w-16 h-16 rounded-full overflow-hidden shadow-sm ring-2 ring-[#dae2fd] bg-[#e2e7ff] flex items-center justify-center">
                                        @if($photo)
                                            <img src="{{ $photo }}" alt="Photo de Dr. {{ $next->doctor->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="font-display font-bold text-[#004ac6]" aria-hidden="true">{{ strtoupper(substr($next->doctor->user->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="space-y-1 min-w-0">
                                    <h2 class="font-display text-[20px] leading-7 font-bold">Dr. {{ $next->doctor->user->name }}</h2>
                                    <p class="text-[13px] text-[#004ac6] font-semibold">{{ $next->doctor->speciality->name }}</p>
                                    <div class="flex items-center gap-1 text-[#434655] text-[13px] pt-1">
                                        <span class="material-symbols-outlined text-[18px] text-[#006242]" aria-hidden="true">location_on</span>
                                        <span class="truncate">{{ $next->doctor->address ?: 'Adresse non renseignée' }}</span>
                                    </div>
                                    @include('patient.partials.status-badge', ['appointment' => $next])
                                </div>
                            </div>
                            <div class="md:col-span-5 bg-[#f2f3ff] p-4 rounded-xl space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] text-[#434655] font-medium">Mode</span>
                                    <span class="text-[11px] text-[#004ac6] font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px]" aria-hidden="true">apartment</span> Présentiel
                                    </span>
                                </div>
                                <p class="font-display text-[20px] leading-7 font-bold">{{ $times['start']->translatedFormat('D j M') }}, {{ $times['start']->format('H:i') }}</p>
                                <p class="text-[11px] text-[#434655]">Durée : {{ $duration }} min</p>
                            </div>
                        </div>
                        <div class="mt-6 pt-4 flex flex-wrap items-center gap-2 bg-[#faf8ff] p-2 rounded-xl">
                            @if($maps)
                                <a href="{{ $maps }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1.5 px-4 h-10 bg-[#004ac6] text-white rounded-lg text-[12px] font-semibold hover:bg-[#003ea8]">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">near_me</span>
                                    Itinéraire
                                </a>
                            @else
                                <button type="button" class="flex items-center gap-1.5 px-4 h-10 bg-[#004ac6] text-white rounded-lg text-[12px] font-semibold"
                                        @click="Alpine.$data(document.body).showToast('Aucune adresse n’est enregistrée pour ce cabinet.')">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">near_me</span>
                                    Itinéraire
                                </button>
                            @endif
                            <button type="button" class="flex items-center gap-1.5 px-4 h-10 bg-[#e2e7ff] text-[#131b2e] hover:bg-[#dae2fd] rounded-lg text-[12px] font-medium"
                                    @click="Alpine.$data(document.body).downloadIcs({{ \Illuminate\Support\Js::from($icsFor($next)) }})">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">calendar_add_on</span>
                                Ajouter au calendrier
                            </button>
                            <a href="{{ route('patient.appointments.show', $next) }}" class="flex items-center gap-1.5 px-4 h-10 bg-white text-[#434655] hover:text-[#131b2e] rounded-lg text-[12px] font-medium shadow-sm">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">visibility</span>
                                Détail
                            </a>
                            <button type="button" class="flex items-center gap-1.5 px-4 h-10 text-[#434655] hover:text-[#131b2e] rounded-lg text-[12px] font-medium"
                                    @click="Alpine.$data(document.body).showToast('La messagerie médecin n’est pas gérée.')">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chat</span>
                                Message
                            </button>
                            <div class="ml-auto">
                                @include('patient.partials.cancel-form', ['appointment' => $next, 'label' => 'Annuler', 'buttonClass' => 'flex items-center gap-1.5 px-4 h-10 text-[#ba1a1a] hover:bg-[#ffdad6] rounded-lg text-[12px] font-medium'])
                            </div>
                        </div>
                    </article>
                @else
                    <article class="bg-white rounded-xl shadow-md p-8 text-center">
                        <span class="material-symbols-outlined text-4xl text-[#c3c6d7]" aria-hidden="true">event_available</span>
                        <p class="mt-3 font-display text-[20px] font-bold">Aucun rendez-vous à venir</p>
                        <p class="text-[15px] text-[#434655] mt-1">Trouvez un médecin et réservez un créneau encore libre.</p>
                        <a href="{{ route('patient.doctors.index') }}" class="inline-flex items-center gap-1 mt-4 text-[14px] font-semibold text-[#004ac6]">Trouver un médecin</a>
                    </article>
                @endif

                <section class="bg-white rounded-xl shadow-sm p-6 lg:p-8 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="font-display text-[20px] leading-7 font-bold">Gestion des rendez-vous</h2>
                            <p class="text-[13px] text-[#434655]">Consultez vos séances enregistrées, sans paiement ni ordonnance fictifs.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-1 bg-[#f2f3ff] p-1 rounded-xl">
                            <button type="button" class="px-3 py-1.5 rounded-lg text-[11px]"
                                    :class="tab === 'upcoming' ? 'bg-white text-[#004ac6] font-semibold shadow-sm' : 'text-[#434655] font-medium'"
                                    @click="tab = 'upcoming'">À venir ({{ $upcomingCount }})</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg text-[11px]"
                                    :class="tab === 'pending' ? 'bg-white text-[#004ac6] font-semibold shadow-sm' : 'text-[#434655] font-medium'"
                                    @click="tab = 'pending'">En attente ({{ $pendingAppointments }})</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg text-[11px]"
                                    :class="tab === 'completed' ? 'bg-white text-[#004ac6] font-semibold shadow-sm' : 'text-[#434655] font-medium'"
                                    @click="tab = 'completed'">Terminés ({{ $completedAppointments }})</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg text-[11px]"
                                    :class="tab === 'cancelled' ? 'bg-white text-[#004ac6] font-semibold shadow-sm' : 'text-[#434655] font-medium'"
                                    @click="tab = 'cancelled'">Annulés ({{ $cancelledAppointments }})</button>
                        </div>
                    </div>

                    <div class="space-y-4 pt-1" x-show="tab === 'upcoming'">
                        @forelse($upcomingAppointments as $apt)
                            @include('patient.partials.dashboard-appointment-row', ['appointment' => $apt])
                        @empty
                            <p class="text-sm text-[#434655] py-6 text-center">Aucun rendez-vous à venir.</p>
                        @endforelse
                    </div>
                    <div class="space-y-4 pt-1" x-show="tab === 'pending'" x-cloak>
                        @forelse($pendingList as $apt)
                            @include('patient.partials.dashboard-appointment-row', ['appointment' => $apt])
                        @empty
                            <p class="text-sm text-[#434655] py-6 text-center">Aucune demande en attente.</p>
                        @endforelse
                    </div>
                    <div class="space-y-4 pt-1" x-show="tab === 'completed'" x-cloak>
                        @forelse($pastAppointments as $apt)
                            @include('patient.partials.dashboard-appointment-row', ['appointment' => $apt])
                        @empty
                            <p class="text-sm text-[#434655] py-6 text-center">Aucune consultation terminée.</p>
                        @endforelse
                    </div>
                    <div class="space-y-4 pt-1" x-show="tab === 'cancelled'" x-cloak>
                        @forelse($cancelledList as $apt)
                            @include('patient.partials.dashboard-appointment-row', ['appointment' => $apt])
                        @empty
                            <p class="text-sm text-[#434655] py-6 text-center">Aucun rendez-vous annulé ou refusé.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('patient.appointments.index') }}" class="inline-block text-[13px] font-semibold text-[#004ac6] hover:underline">Voir tout</a>
                </section>
            </div>

            <aside class="lg:col-span-4 space-y-6">
                <div class="p-6 bg-[#dae2fd]/50 rounded-xl space-y-4 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-[#006242] text-white flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">info</span>
                        </span>
                        <span class="text-[11px] uppercase tracking-wider text-[#006242] font-bold">Information</span>
                    </div>
                    <div>
                        <h2 class="font-display text-[16px] leading-6 font-bold">Aucun rappel de prévention</h2>
                        <p class="text-[13px] leading-5 text-[#434655] mt-1">SantéConnect n’enregistre pas de rappel dentaire ni de note fictive.</p>
                    </div>
                    <a href="{{ route('patient.doctors.index') }}" class="w-full py-2 bg-[#006a61] hover:bg-[#005049] text-white rounded-lg text-[11px] font-semibold flex items-center justify-center gap-1">
                        Ouvrir l’annuaire
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_forward</span>
                    </a>
                </div>

                <div class="p-6 bg-white rounded-xl shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-[16px] leading-6 font-bold">Vos médecins</h2>
                        <a class="text-[11px] text-[#004ac6] hover:underline font-semibold" href="{{ route('patient.doctors.index') }}">Voir tout</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($careTeam as $doctor)
                            @php
                                $slot = $doctor->availabilities->first();
                                $bookUrl = $slot
                                    ? route('patient.appointments.create', ['availability_id' => $slot->id])
                                    : route('patient.doctors.show', $doctor);
                                $teamPhoto = $doctor->user->profile_image_url ?? ($doctor->photo ? asset('storage/'.$doctor->photo) : null);
                            @endphp
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-[#e2e7ff] flex items-center justify-center shrink-0">
                                        @if($teamPhoto)
                                            <img src="{{ $teamPhoto }}" alt="Photo de Dr. {{ $doctor->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xs font-bold text-[#004ac6]" aria-hidden="true">{{ strtoupper(substr($doctor->user->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[14px] font-semibold truncate">Dr. {{ $doctor->user->name }}</p>
                                        <p class="text-[13px] text-[#434655] truncate">{{ $doctor->speciality->name }}</p>
                                    </div>
                                </div>
                                <a href="{{ $bookUrl }}" class="shrink-0 px-3 py-1.5 rounded-lg bg-[#eaedff] text-[#004ac6] hover:bg-[#004ac6] hover:text-white text-[11px] font-medium">Réserver</a>
                            </div>
                        @empty
                            <p class="text-[13px] text-[#434655]">Aucun médecin n’est encore lié à vos rendez-vous.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('patient.doctors.index') }}" class="w-full py-2.5 rounded-lg bg-[#e2e7ff] text-[#004ac6] hover:bg-[#dae2fd] text-[12px] font-semibold flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
                        Trouver un médecin
                    </a>
                </div>

                <div class="p-6 bg-white rounded-xl shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-semibold">Cabinet du prochain rendez-vous</span>
                    </div>
                    @if($next && $next->doctor->address)
                        <p class="text-[13px] text-[#434655]">{{ $next->doctor->address }}</p>
                        <a class="text-[11px] text-[#004ac6] hover:underline font-semibold" href="{{ 'https://www.google.com/maps/search/?'.http_build_query(['api' => 1, 'query' => $next->doctor->address]) }}" target="_blank" rel="noopener noreferrer">Ouvrir dans Maps</a>
                    @else
                        <p class="text-[13px] text-[#434655]">Aucune adresse de cabinet n’est enregistrée. Aucune carte n’est intégrée ici.</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
