@php
    $nextAppointment ??= null;
    $totalCount ??= $appointments->total();
    $pendingCount ??= 0;
    $upcomingAcceptedCount ??= 0;
    $completedCount ??= 0;
    $cancelledCount ??= 0;
    $rejectedCount ??= 0;
    $missedCount ??= 0;
    $currentStatus = $currentStatus ?? request('status', '');
    $currentSearch = $currentSearch ?? request('search', '');
    $currentPeriod = $currentPeriod ?? request('period', '');
    $currentDir = $currentDir ?? request('direction', 'desc');

    $when = function ($appointment) {
        $start = $appointment->availability
            ? $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->start_time)
            : $appointment->appointment_date;
        $end = $appointment->availability
            ? $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->end_time)
            : $appointment->appointment_date->copy()->addHour();

        return compact('start', 'end');
    };
    $photoFor = function ($appointment) {
        return $appointment->doctor->user->profile_image_url
            ?? ($appointment->doctor->photo ? asset('storage/'.$appointment->doctor->photo) : null);
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
    $filterQuery = function (?string $status = null) use ($currentDir, $currentSearch, $currentPeriod) {
        return array_filter([
            'status' => $status ?: null,
            'direction' => $currentDir !== 'desc' ? $currentDir : null,
            'search' => $currentSearch !== '' ? $currentSearch : null,
            'period' => $currentPeriod !== '' ? $currentPeriod : null,
        ]);
    };
    $statusFilters = [
        ['val' => '', 'label' => 'Tous', 'count' => $totalCount, 'pill' => 'bg-[#e2e7ff] text-[#434655]'],
        ['val' => 'upcoming', 'label' => 'À venir', 'count' => $upcomingAcceptedCount, 'pill' => 'bg-white/20 text-white'],
        ['val' => 'pending', 'label' => 'En attente', 'count' => $pendingCount, 'pill' => 'bg-[#fef3c7] text-[#92400e]'],
        ['val' => 'completed', 'label' => 'Terminés', 'count' => $completedCount, 'pill' => 'bg-[#e2e7ff] text-[#434655]'],
        ['val' => 'cancelled', 'label' => 'Annulés', 'count' => $cancelledCount, 'pill' => 'bg-[#e2e7ff] text-[#434655]'],
        ['val' => 'rejected', 'label' => 'Refusés', 'count' => $rejectedCount, 'pill' => 'bg-[#ffdad6] text-[#93000a]'],
        ['val' => 'missed', 'label' => 'Non réalisés', 'count' => $missedCount, 'pill' => 'bg-[#e2e7ff] text-[#434655]'],
    ];
@endphp

@extends('layouts.app')
@section('title', 'Mes rendez-vous')

@section('content')
<div class="bg-[#faf8ff] text-[#131b2e] min-h-screen">
    <div class="sc-page sc-stack">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <nav aria-label="Fil d'ariane" class="flex items-center gap-1 text-[13px] leading-5 text-[#434655]">
                    <a class="hover:text-[#004ac6] transition-colors flex items-center gap-1" href="{{ route('patient.dashboard') }}">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">home</span>
                        Accueil
                    </a>
                    <span class="text-[#c3c6d7]">/</span>
                    <span class="text-[#131b2e] text-[12px] font-semibold">Mes rendez-vous</span>
                </nav>
                <div class="flex items-baseline gap-2 flex-wrap mt-1">
                    <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 tracking-tight">Mes rendez-vous</h1>
                    <span class="px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[#004ac6] text-[11px] font-medium">Compte #{{ Auth::id() }}</span>
                </div>
                <p class="text-[15px] leading-6 text-[#434655] max-w-2xl">
                    Consultez et gérez vos consultations programmées ou passées.
                </p>
            </div>
            <a class="inline-flex items-center gap-1 px-4 py-2.5 rounded-xl bg-[#004ac6] text-white text-[14px] font-semibold shadow-sm hover:bg-[#003ea8] transition-all self-start md:self-auto shrink-0"
               href="{{ route('patient.doctors.index') }}">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add_circle</span>
                Prendre un rendez-vous
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-white shadow-sm flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <span class="text-[12px] font-semibold text-[#434655]">Total des rendez-vous</span>
                    <span class="font-display text-[28px] leading-9">{{ $totalCount }}</span>
                    <span class="text-[11px] text-[#434655] flex items-center gap-1 mt-0.5">
                        <span class="material-symbols-outlined text-[14px] text-[#004ac6]" aria-hidden="true">calendar_month</span>
                        Enregistrés sur votre compte
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#f2f3ff] flex items-center justify-center text-[#004ac6] shrink-0">
                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">event_note</span>
                </div>
            </div>
            <div class="p-4 rounded-xl bg-white shadow-sm flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <span class="text-[12px] font-semibold text-[#434655]">À venir / Confirmés</span>
                    <span class="font-display text-[28px] leading-9 text-[#006242]">{{ $upcomingAcceptedCount }}</span>
                    @if($nextAppointment && $nextAppointment->status === 'accepted')
                        @php
                            $nextDays = (int) now()->startOfDay()->diffInDays($nextAppointment->appointment_date->copy()->startOfDay());
                            $nextLabel = $nextDays < 1
                                ? 'Aujourd’hui'
                                : 'Prochain dans '.$nextDays.' jour'.($nextDays > 1 ? 's' : '');
                        @endphp
                        <span class="text-[11px] text-[#006242] flex items-center gap-1 mt-0.5 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#006242] animate-pulse"></span>
                            {{ $nextLabel }}
                        </span>
                    @else
                        <span class="text-[11px] text-[#434655] flex items-center gap-1 mt-0.5">Aucun créneau confirmé à venir</span>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#bdffdb]/60 flex items-center justify-center text-[#006242] shrink-0">
                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">check_circle</span>
                </div>
            </div>
            <div class="p-4 rounded-xl bg-white shadow-sm flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <span class="text-[12px] font-semibold text-[#434655]">En attente</span>
                    <span class="font-display text-[28px] leading-9 text-[#b45309]">{{ $pendingCount }}</span>
                    <span class="text-[11px] text-[#434655] flex items-center gap-1 mt-0.5">
                        <span class="material-symbols-outlined text-[14px] text-[#d97706]" aria-hidden="true">schedule</span>
                        {{ $pendingCount === 1 ? 'Demande en cours' : 'Demandes en cours' }}
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#fef3c7] flex items-center justify-center text-[#b45309] shrink-0">
                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">hourglass_top</span>
                </div>
            </div>
            <div class="p-4 rounded-xl bg-white shadow-sm flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <span class="text-[12px] font-semibold text-[#434655]">Historique terminé</span>
                    <span class="font-display text-[28px] leading-9">{{ $completedCount }}</span>
                    <span class="text-[11px] text-[#434655] flex items-center gap-1 mt-0.5">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">inventory_2</span>
                        Consultations clôturées
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#f2f3ff] flex items-center justify-center text-[#434655] shrink-0">
                    <span class="material-symbols-outlined text-[26px]" aria-hidden="true">history</span>
                </div>
            </div>
        </div>

        @if($nextAppointment && $currentSearch === '' && $currentPeriod === '' && in_array($currentStatus, ['', 'upcoming', $nextAppointment->status], true))
            @php
                $nextTimes = $when($nextAppointment);
                $nextPhoto = $photoFor($nextAppointment);
                $nextDuration = max(1, (int) $nextTimes['start']->diffInMinutes($nextTimes['end']));
                $nextCountdown = $nextTimes['start']->isFuture()
                    ? ($nextTimes['start']->diffInHours(now()) >= 24
                        ? 'Dans '.$nextTimes['start']->diffInDays(now()).' jours'
                        : 'Dans '.$nextTimes['start']->diffInHours(now()).' h')
                    : 'Horaire passé';
                $canEditNext = in_array($nextAppointment->status, ['pending', 'accepted'], true)
                    && $nextAppointment->appointment_date->isFuture();
            @endphp
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">upcoming</span>
                        <h2 class="font-display text-[16px] leading-6">Prochaine consultation programmée</h2>
                    </div>
                </div>
                <div class="relative bg-white rounded-2xl p-4 sm:p-6 shadow-md overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#004ac6] via-[#2563eb] to-[#006242]"></div>
                    <div class="flex flex-col lg:flex-row gap-6 lg:items-center justify-between pt-2">
                        <div class="flex flex-col sm:flex-row gap-4 items-start">
                            <div class="relative shrink-0">
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden shadow-sm bg-[#eaedff] flex items-center justify-center">
                                    @if($nextPhoto)
                                        <img class="w-full h-full object-cover" src="{{ $nextPhoto }}" alt="Photo de Dr. {{ $nextAppointment->doctor->user->name }}">
                                    @else
                                        <span class="font-display text-[28px] font-bold text-[#004ac6]" aria-hidden="true">{{ strtoupper(substr($nextAppointment->doctor->user->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-1 flex-wrap">
                                    @include('patient.partials.status-badge', ['appointment' => $nextAppointment])
                                    <span class="px-2.5 py-0.5 rounded-full bg-[#f2f3ff] text-[#434655] text-[11px] font-medium">
                                        Rendez-vous #{{ $nextAppointment->id }}
                                    </span>
                                    <span class="text-[#004ac6] text-[11px] font-semibold">{{ $nextCountdown }}</span>
                                </div>
                                <h3 class="font-display text-[20px] leading-7 mt-1">Dr. {{ $nextAppointment->doctor->user->name }}</h3>
                                <p class="text-[13px] leading-5 text-[#434655]">
                                    {{ $nextAppointment->doctor->speciality->name }}
                                    @if($nextAppointment->doctor->address)
                                        • <span class="text-[#131b2e] font-medium">{{ $nextAppointment->doctor->address }}</span>
                                    @endif
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 pt-2 bg-[#f2f3ff]/80 rounded-xl p-3">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[#004ac6] shrink-0">
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">calendar_today</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[11px] text-[#434655]">Date &amp; Heure</span>
                                            <span class="text-[12px] text-[#131b2e] font-bold">{{ $nextTimes['start']->locale('fr')->translatedFormat('l j F Y') }} à {{ $nextTimes['start']->format('H:i') }}</span>
                                            <span class="text-[11px] text-[#434655]">Durée : {{ $nextDuration }} min</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[#004ac6] shrink-0">
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">location_on</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[11px] text-[#434655]">Lieu de consultation</span>
                                            <span class="text-[12px] text-[#131b2e] font-bold">{{ $nextAppointment->doctor->address ?: 'Adresse non renseignée' }}</span>
                                            <span class="text-[11px] text-[#434655]">Présentiel</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row lg:flex-col gap-2.5 shrink-0 justify-end w-full lg:w-60">
                            <a class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-[#004ac6] text-white text-[12px] font-semibold shadow-sm hover:bg-[#003ea8] transition-colors"
                               href="{{ route('patient.appointments.show', $nextAppointment) }}">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">confirmation_number</span>
                                Voir les détails &amp; Pass
                            </a>
                            <button class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold transition-colors"
                                    type="button"
                                    @click="Alpine.$data(document.body).downloadIcs({{ \Illuminate\Support\Js::from($icsFor($nextAppointment)) }})">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit_calendar</span>
                                Ajouter au calendrier (.ics)
                            </button>
                            @if($canEditNext)
                                <a class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold transition-colors"
                                   href="{{ route('patient.appointments.edit', $nextAppointment) }}">
                                    Modifier les notes
                                </a>
                            @endif
                            @include('patient.partials.cancel-form', [
                                'appointment' => $nextAppointment,
                                'label' => 'Annuler',
                                'buttonClass' => 'w-full text-center text-[#ba1a1a] hover:text-[#93000a] hover:bg-[#ffdad6]/40 py-2 rounded-lg text-[11px] font-medium transition-colors',
                            ])
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('patient.appointments.index') }}" class="flex flex-col gap-4 bg-white p-4 rounded-2xl shadow-sm">
            @if($currentStatus !== '')
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            @if($currentDir !== 'desc')
                <input type="hidden" name="direction" value="{{ $currentDir }}">
            @endif
            <div class="sc-toolbar">
                <div class="sc-scroll-row" role="navigation" aria-label="Filtrer par statut">
                    @foreach($statusFilters as $f)
                        <a href="{{ route('patient.appointments.index', $filterQuery($f['val'])) }}"
                           @class([
                               'px-3.5 py-2 rounded-lg text-[12px] transition-colors flex items-center gap-1.5 shrink-0',
                               'bg-[#004ac6] text-white shadow-sm font-semibold' => $currentStatus === $f['val'],
                               'text-[#434655] hover:text-[#131b2e] hover:bg-[#f2f3ff] font-medium' => $currentStatus !== $f['val'],
                           ])
                           @if($currentStatus === $f['val']) aria-current="page" @endif>
                            {{ $f['label'] }}
                            <span @class([
                                'px-1.5 rounded-full text-[11px]',
                                $currentStatus === $f['val'] ? 'bg-white/20 text-white font-bold' : $f['pill'],
                            ])>{{ $f['count'] }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <div class="relative flex-1 sm:w-72">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#434655] text-[18px]" aria-hidden="true">search</span>
                        <input class="w-full pl-9 pr-3 py-2 bg-[#f2f3ff] focus:bg-white rounded-lg text-[13px] text-[#131b2e] placeholder:text-[#434655] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/30"
                               name="search"
                               placeholder="Médecin, spécialité..."
                               type="search"
                               value="{{ $currentSearch }}">
                    </div>
                    <div class="relative shrink-0 w-full sm:w-52">
                        <x-ui.select name="period" label="Filtrer par date" onchange="this.form.submit()">
                            <option value="" @selected($currentPeriod === '')>Toutes les dates</option>
                            <option value="month" @selected($currentPeriod === 'month')>Ce mois</option>
                            <option value="3months" @selected($currentPeriod === '3months')>3 derniers mois</option>
                            <option value="year" @selected($currentPeriod === 'year')>Cette année</option>
                        </x-ui.select>
                    </div>
                    <button type="submit" class="h-10 px-4 rounded-lg bg-[#004ac6] text-white text-[12px] font-semibold shrink-0">
                        Rechercher
                    </button>
                    <div class="flex items-center gap-1">
                        <a href="{{ request()->fullUrlWithQuery(['direction' => 'desc', 'page' => 1]) }}"
                           @class([
                               'px-3 py-1.5 rounded-lg text-[11px] font-semibold border transition-colors',
                               $currentDir === 'desc' ? 'bg-[#004ac6] text-white border-[#004ac6]' : 'bg-white border-[#c3c6d7] text-[#434655] hover:border-[#004ac6]',
                           ])>Récent d'abord</a>
                        <a href="{{ request()->fullUrlWithQuery(['direction' => 'asc', 'page' => 1]) }}"
                           @class([
                               'px-3 py-1.5 rounded-lg text-[11px] font-semibold border transition-colors',
                               $currentDir === 'asc' ? 'bg-[#004ac6] text-white border-[#004ac6]' : 'bg-white border-[#c3c6d7] text-[#434655] hover:border-[#004ac6]',
                           ])>Ancien d'abord</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-[16px] leading-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#006a61] text-[20px]" aria-hidden="true">pending_actions</span>
                    Vos consultations
                </h2>
                <span class="text-[11px] text-[#434655]">{{ $appointments->total() }} résultat{{ $appointments->total() > 1 ? 's' : '' }}</span>
            </div>

            <div class="flex flex-col gap-3">
                @forelse($appointments as $appointment)
                    @php
                        $times = $when($appointment);
                        $photo = $photoFor($appointment);
                        $faded = in_array($appointment->status, ['cancelled', 'rejected', 'missed'], true);
                        $pastDone = $appointment->status === 'completed';
                        $canEdit = in_array($appointment->status, ['pending', 'accepted'], true)
                            && $appointment->appointment_date->isFuture();
                    @endphp
                    <article @class([
                        'bg-white rounded-2xl p-4 shadow-sm transition-all hover:shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4',
                        'opacity-80' => $faded,
                    ])>
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="relative shrink-0">
                                <div @class([
                                    'w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden bg-[#eaedff] flex items-center justify-center',
                                    'grayscale-[20%]' => $pastDone,
                                ])>
                                    @if($photo)
                                        <img class="w-full h-full object-cover" src="{{ $photo }}" alt="Photo de Dr. {{ $appointment->doctor->user->name }}">
                                    @else
                                        <span class="font-display font-bold text-[#004ac6]" aria-hidden="true">{{ strtoupper(substr($appointment->doctor->user->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col gap-1 min-w-0">
                                <div class="flex items-center gap-1 flex-wrap">
                                    @include('patient.partials.status-badge', ['appointment' => $appointment])
                                    <span class="text-[#434655] text-[11px]">Présentiel{{ $appointment->doctor->address ? ' • '.$appointment->doctor->address : '' }}</span>
                                </div>
                                <h3 class="font-display text-[16px] leading-6">Dr. {{ $appointment->doctor->user->name }}</h3>
                                <p class="text-[13px] leading-5 text-[#434655]">
                                    {{ $appointment->doctor->speciality->name }} • {{ $times['start']->locale('fr')->translatedFormat('j F Y') }} à {{ $times['start']->format('H:i') }}
                                </p>
                                @if($appointment->availability)
                                    <p class="text-[11px] text-[#434655] flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-[#004ac6]" aria-hidden="true">schedule</span>
                                        {{ substr($appointment->availability->start_time, 0, 5) }} – {{ substr($appointment->availability->end_time, 0, 5) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1 flex-wrap self-end md:self-center shrink-0 w-full sm:w-auto justify-end">
                            <a class="px-3.5 py-2 rounded-xl bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold transition-colors"
                               href="{{ route('patient.appointments.show', $appointment) }}">
                                {{ $appointment->status === 'completed' ? 'Récapitulatif' : 'Voir détails' }}
                            </a>
                            @if($canEdit)
                                <a class="px-3.5 py-2 rounded-xl bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold transition-colors"
                                   href="{{ route('patient.appointments.edit', $appointment) }}">
                                    Modifier
                                </a>
                            @endif
                            @if($appointment->status === 'completed')
                                <a class="px-3 py-2 rounded-xl bg-[#004ac6]/10 hover:bg-[#004ac6]/20 text-[#004ac6] text-[12px] font-semibold transition-colors"
                                   href="{{ route('patient.doctors.show', $appointment->doctor) }}">
                                    Reprendre RDV
                                </a>
                            @else
                                @include('patient.partials.cancel-form', [
                                    'appointment' => $appointment,
                                    'label' => 'Annuler',
                                    'buttonClass' => 'px-3 py-2 rounded-xl text-[#ba1a1a] hover:bg-[#ffdad6]/40 text-[12px] font-semibold transition-colors',
                                ])
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-2xl py-16 text-center shadow-sm">
                        <span class="material-symbols-outlined text-6xl text-[#c3c6d7] block mb-4" aria-hidden="true">calendar_today</span>
                        @if(request()->filled('status') || request()->filled('search') || request()->filled('period'))
                            <p class="font-semibold text-[#131b2e] mb-1">Aucun rendez-vous pour ce filtre</p>
                            <p class="text-sm text-[#434655] mb-6">Essayez un autre statut ou affichez tous vos rendez-vous.</p>
                            <a href="{{ route('patient.appointments.index') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#e2e7ff] rounded-xl text-sm font-semibold text-[#004ac6]">
                                Voir tous les rendez-vous
                            </a>
                        @else
                            <p class="font-semibold text-[#131b2e] mb-1">Aucun rendez-vous pour le moment</p>
                            <p class="text-sm text-[#434655] mb-6">Réservez un créneau auprès d’un médecin validé.</p>
                            <a href="{{ route('patient.doctors.index') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#004ac6] text-white rounded-xl text-sm font-semibold hover:bg-[#003ea8]">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">search</span>
                                Trouver un médecin
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            @if($appointments->total() > 0)
                <div class="bg-white p-4 rounded-2xl shadow-sm sc-pager">
                    <p class="sc-pager__label">
                        Affichage de <span class="font-semibold text-[#131b2e]">{{ $appointments->firstItem() }}</span>
                        à <span class="font-semibold text-[#131b2e]">{{ $appointments->lastItem() }}</span>
                        sur <span class="font-semibold text-[#131b2e]">{{ $appointments->total() }}</span> rendez-vous
                    </p>
                    <div class="sc-pager__pages">{{ $appointments->links() }}</div>
                </div>
            @endif
        </div>

        <div class="bg-gradient-to-r from-[#dbe1ff] to-[#e2e7ff] p-4 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#004ac6] text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">support_agent</span>
                </div>
                <div class="flex flex-col">
                    <h2 class="font-display text-[16px] leading-6">Besoin d’aide pour un créneau ?</h2>
                    <p class="text-[13px] leading-5 text-[#434655]">En cas d’urgence vitale, appelez le 15 ou le 112. SantéConnect ne remplace pas un service d’urgence.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
