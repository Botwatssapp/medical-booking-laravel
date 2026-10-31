@extends('layouts.app')
@section('title', 'Dr. '.$doctor->user->name)

@php
    $bookable = $availabilities->where('is_available', true);
    $firstBookable = $bookable->first();
    $bookableDays = $bookable->groupBy(fn ($a) => $a->date->format('Y-m-d'));
    $widgetDays = $bookableDays->take(4);
    $widgetSlots = $widgetDays->flatten(1);
    $photo = $doctor->user->profile_image_url
        ?? ($doctor->photo ? asset('storage/'.$doctor->photo) : null);
    $durations = $bookable->map(function ($slot) {
        try {
            return (int) \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time));
        } catch (\Throwable) {
            return null;
        }
    })->filter()->unique();
    $slotDuration = $durations->count() === 1 ? $durations->first() : null;
    $maps = $doctor->address
        ? 'https://www.google.com/maps/search/?'.http_build_query(['api' => 1, 'query' => $doctor->address])
        : null;
    $slotLinks = $widgetSlots->mapWithKeys(fn ($slot) => [
        $slot->id => route('patient.appointments.create', ['availability_id' => $slot->id]),
    ]);
    $slotMeta = $widgetSlots->mapWithKeys(fn ($slot) => [
        $slot->id => [
            'date' => $slot->date->format('d/m/Y'),
            'time' => substr($slot->start_time, 0, 5),
            'day' => $slot->date->format('Y-m-d'),
        ],
    ]);
    $initialDay = $firstBookable?->date->format('Y-m-d') ?? 'all';
    $agendaMonth = $firstBookable?->date->translatedFormat('F Y');
@endphp

@section('content')
<div class="bg-[#faf8ff] min-h-screen overflow-x-hidden text-[#131b2e] antialiased"
     x-data="{
        saved: false,
        bioOpen: false,
        tab: 'overview',
        pickerDay: {{ \Illuminate\Support\Js::from($initialDay) }},
        selectedId: {{ $firstBookable?->id ?? 'null' }},
        links: {{ \Illuminate\Support\Js::from($slotLinks) }},
        meta: {{ \Illuminate\Support\Js::from($slotMeta) }},
        checkoutUrl() {
            return this.links[this.selectedId] || {{ \Illuminate\Support\Js::from($firstBookable ? route('patient.appointments.create', ['availability_id' => $firstBookable->id]) : route('patient.doctors.index')) }};
        },
        selectedLabel() {
            const item = this.meta[this.selectedId];
            return item ? (item.date + ' · ' + item.time) : '';
        }
     }">

    <section class="w-full bg-[#f2f3ff] py-2 px-4 lg:px-8">
        <div class="sc-page-wide flex flex-col md:flex-row md:items-center justify-between gap-3">
            <nav class="flex items-center gap-1 text-[12px] font-semibold tracking-[0.02em] text-[#434655] flex-wrap" aria-label="Fil d’Ariane">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-[#004ac6] transition-colors">Accueil</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <a href="{{ route('patient.doctors.index') }}" class="hover:text-[#004ac6] transition-colors">Annuaire des spécialistes</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <a href="{{ route('patient.doctors.index', ['speciality' => $doctor->speciality_id]) }}" class="hover:text-[#004ac6] transition-colors">{{ $doctor->speciality->name }}</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span class="text-[#131b2e] font-semibold">Dr. {{ $doctor->user->name }}</span>
            </nav>
            <div class="flex items-center gap-2 flex-wrap">
                @if($bookable->isNotEmpty())
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-[#89f5e7] text-[#00201d] text-[11px] font-semibold shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-[#006a61] animate-pulse"></span>
                        Nouveaux patients • Consultation en cabinet
                    </span>
                @endif
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-[#dae2fd] text-[#004ac6] text-[11px] font-semibold shadow-sm">
                    <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">verified</span>
                    Profil validé
                </span>
            </div>
        </div>
    </section>

    <div class="sc-page-wide py-6 sm:py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 flex flex-col gap-6 min-w-0">
                <section class="bg-white rounded-xl p-6 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#dbe1ff] opacity-30 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="flex flex-col sm:flex-row gap-6 items-start relative z-10">
                        <div class="relative shrink-0">
                            <div class="w-32 h-32 md:w-36 md:h-36 rounded-xl overflow-hidden shadow-md bg-[#e2e7ff]">
                                @if($photo)
                                    <img src="{{ $photo }}" alt="Photo de Dr. {{ $doctor->user->name }}" class="w-full h-full object-cover object-top">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#004ac6] text-4xl font-bold font-display" aria-hidden="true">
                                        {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="absolute -bottom-2 -right-2 bg-[#004ac6] text-white rounded-full p-1 shadow-md flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">verified_user</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="px-2.5 py-0.5 rounded-full bg-[#dbe1ff] text-[#00174b] text-[11px] font-semibold">{{ $doctor->speciality->name }}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#eaedff] text-[#434655] text-[11px] font-semibold">Consultation en cabinet</span>
                            </div>
                            <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight text-[#131b2e]">
                                Dr. {{ $doctor->user->name }}
                            </h1>
                            <p class="text-[15px] leading-6 text-[#434655] font-medium mt-0.5">
                                {{ $doctor->speciality->name }} • Consultation en cabinet
                            </p>
                            @if($doctor->address)
                                <div class="flex items-center gap-1.5 mt-1 text-[13px] leading-5 text-[#434655]">
                                    <span class="material-symbols-outlined text-[18px] text-[#004ac6] shrink-0" aria-hidden="true">local_hospital</span>
                                    <span class="truncate">{{ $doctor->address }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-6 pt-4 bg-[#f2f3ff] rounded-xl p-2">
                        <div class="flex flex-col items-center justify-center p-1 text-center">
                            <div class="flex items-center gap-1 text-[#004ac6]">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">event_available</span>
                                <span class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">{{ $bookable->count() }}</span>
                            </div>
                            <span class="text-[11px] font-medium text-[#434655]">Créneaux libres</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-1 text-center">
                            <span class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">
                                {{ $firstBookable ? $firstBookable->date->format('d/m') : '—' }}
                            </span>
                            <span class="text-[11px] font-medium text-[#434655]">
                                {{ $firstBookable ? 'Prochain '.substr($firstBookable->start_time, 0, 5) : 'Aucun créneau' }}
                            </span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-1 text-center">
                            <span class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">{{ $slotDuration ? $slotDuration.' min' : 'Variable' }}</span>
                            <span class="text-[11px] font-medium text-[#434655]">Durée du créneau</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-1 text-center">
                            <div class="flex items-center gap-1 text-[#006242]">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">apartment</span>
                                <span class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Cabinet</span>
                            </div>
                            <span class="text-[11px] font-medium text-[#434655]">Seul mode géré</span>
                        </div>
                    </div>
                </section>

                <nav class="bg-white rounded-xl p-1.5 shadow-sm flex items-center gap-1 overflow-x-auto" aria-label="Sections du profil">
                    <a href="#overview" @click.prevent="tab = 'overview'"
                       class="px-4 py-2 rounded-lg text-[12px] font-semibold whitespace-nowrap transition-colors"
                       :class="tab === 'overview' ? 'bg-[#004ac6] text-white shadow-sm' : 'text-[#434655] hover:bg-[#eaedff] hover:text-[#131b2e]'">
                        Présentation
                    </a>
                    <a href="#credentials" @click.prevent="tab = 'credentials'"
                       class="px-4 py-2 rounded-lg text-[12px] font-medium whitespace-nowrap transition-colors"
                       :class="tab === 'credentials' ? 'bg-[#004ac6] text-white shadow-sm font-semibold' : 'text-[#434655] hover:bg-[#eaedff] hover:text-[#131b2e]'">
                        Parcours
                    </a>
                    <a href="#specialties" @click.prevent="tab = 'specialties'"
                       class="px-4 py-2 rounded-lg text-[12px] font-medium whitespace-nowrap transition-colors"
                       :class="tab === 'specialties' ? 'bg-[#004ac6] text-white shadow-sm font-semibold' : 'text-[#434655] hover:bg-[#eaedff] hover:text-[#131b2e]'">
                        Spécialité
                    </a>
                    @if($doctor->address)
                        <a href="#locations" @click.prevent="tab = 'locations'"
                           class="px-4 py-2 rounded-lg text-[12px] font-medium whitespace-nowrap transition-colors"
                           :class="tab === 'locations' ? 'bg-[#004ac6] text-white shadow-sm font-semibold' : 'text-[#434655] hover:bg-[#eaedff] hover:text-[#131b2e]'">
                            Cabinet &amp; accès
                        </a>
                    @endif
                    <a href="#reviews" @click.prevent="tab = 'reviews'"
                       class="px-4 py-2 rounded-lg text-[12px] font-medium whitespace-nowrap transition-colors"
                       :class="tab === 'reviews' ? 'bg-[#004ac6] text-white shadow-sm font-semibold' : 'text-[#434655] hover:bg-[#eaedff] hover:text-[#131b2e]'">
                        Avis patients
                    </a>
                </nav>

                <section class="bg-white rounded-xl p-6 shadow-sm space-y-4 scroll-mt-24" id="overview">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-[20px] leading-7 font-semibold tracking-tight text-[#131b2e] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6]" aria-hidden="true">medical_information</span>
                            Biographie clinique
                        </h2>
                        <span class="text-[11px] font-semibold text-[#006a61] bg-[#89f5e7] px-2 py-1 rounded-full shrink-0">
                            {{ $doctor->speciality->name }}
                        </span>
                    </div>
                    @if($doctor->bio)
                        <p class="text-[15px] leading-6 text-[#434655] whitespace-pre-line"
                           :class="bioOpen ? '' : 'line-clamp-5'">{{ $doctor->bio }}</p>
                        @if(mb_strlen($doctor->bio) > 220)
                            <button type="button" class="text-[14px] font-semibold text-[#004ac6] hover:underline"
                                    @click="bioOpen = !bioOpen" x-text="bioOpen ? 'Réduire' : 'Lire la suite'"></button>
                        @endif
                    @else
                        <p class="text-[15px] leading-6 text-[#434655] italic">Aucune biographie renseignée.</p>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                        <div class="bg-[#f2f3ff] p-4 rounded-xl space-y-1">
                            <div class="w-8 h-8 rounded-lg bg-[#dbe1ff] text-[#004ac6] flex items-center justify-center mb-2">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">stethoscope</span>
                            </div>
                            <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Spécialité</h3>
                            <p class="text-[13px] leading-5 text-[#434655]">{{ $doctor->speciality->name }}{{ $doctor->speciality->description ? ' — '.$doctor->speciality->description : '' }}</p>
                        </div>
                        <div class="bg-[#f2f3ff] p-4 rounded-xl space-y-1">
                            <div class="w-8 h-8 rounded-lg bg-[#86f2e4] text-[#006f66] flex items-center justify-center mb-2">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">apartment</span>
                            </div>
                            <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Présentiel</h3>
                            <p class="text-[13px] leading-5 text-[#434655]">Consultation en cabinet uniquement. La téléconsultation n’est pas gérée.</p>
                        </div>
                        <div class="bg-[#f2f3ff] p-4 rounded-xl space-y-1">
                            <div class="w-8 h-8 rounded-lg bg-[#6ffbbe] text-[#002113] flex items-center justify-center mb-2">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">event_available</span>
                            </div>
                            <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Demande en ligne</h3>
                            <p class="text-[13px] leading-5 text-[#434655]">Le rendez-vous reste en attente jusqu’à confirmation du médecin.</p>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-xl p-6 shadow-sm space-y-4 scroll-mt-24" id="credentials">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-[20px] leading-7 font-semibold tracking-tight text-[#131b2e] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6]" aria-hidden="true">school</span>
                            Parcours &amp; diplômes
                        </h2>
                        <span class="text-[11px] font-semibold text-[#006242] inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">info</span>
                            Non catalogué
                        </span>
                    </div>
                    <p class="text-[15px] leading-6 text-[#434655]">
                        Aucun diplôme, fellowship, hôpital affilié ni numéro d’ordre n’est enregistré dans SantéConnect.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="p-2 rounded-lg bg-[#f2f3ff] flex items-start gap-2">
                            <span class="material-symbols-outlined text-[#004ac6] text-[22px] shrink-0 mt-0.5" aria-hidden="true">workspace_premium</span>
                            <div>
                                <p class="text-[14px] font-semibold text-[#131b2e]">Diplômes</p>
                                <p class="text-[13px] text-[#434655]">Non renseignés sur ce profil.</p>
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-[#f2f3ff] flex items-start gap-2">
                            <span class="material-symbols-outlined text-[#004ac6] text-[22px] shrink-0 mt-0.5" aria-hidden="true">badge</span>
                            <div>
                                <p class="text-[14px] font-semibold text-[#131b2e]">Numéro d’ordre</p>
                                <p class="text-[13px] text-[#434655]">Aucun identifiant professionnel n’est stocké ici.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-xl p-6 shadow-sm space-y-4 scroll-mt-24" id="specialties">
                    <h2 class="font-display text-[20px] leading-7 font-semibold tracking-tight text-[#131b2e] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#004ac6]" aria-hidden="true">cardiology</span>
                        Spécialité &amp; actes
                    </h2>
                    <div>
                        <p class="text-[12px] font-semibold uppercase tracking-wider text-[#434655] block mb-2">Spécialisation enregistrée</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1.5 rounded-full bg-[#eaedff] text-[#131b2e] text-[14px] font-medium">{{ $doctor->speciality->name }}</span>
                        </div>
                    </div>
                    <div class="pt-2">
                        <p class="text-[12px] font-semibold uppercase tracking-wider text-[#434655] block mb-2">Équipements &amp; procédures</p>
                        <p class="text-[13px] leading-5 text-[#434655]">Aucun équipement ni acte n’est catalogué dans l’annuaire. Une seule spécialité est associée à ce profil.</p>
                    </div>
                </section>

                <section class="bg-white rounded-xl p-6 shadow-sm space-y-4 scroll-mt-24" id="locations">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-[20px] leading-7 font-semibold tracking-tight text-[#131b2e] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6]" aria-hidden="true">location_on</span>
                            Cabinet, tarifs &amp; accès
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Cabinet</h3>
                                <p class="text-[15px] leading-6 text-[#434655] mt-1 break-words">
                                    {{ $doctor->address ?: 'Adresse du cabinet non renseignée.' }}
                                </p>
                                @if($doctor->phone)
                                    <a href="tel:{{ $doctor->phone }}" class="mt-2 inline-flex items-center gap-1.5 text-[14px] font-semibold text-[#004ac6] hover:underline">
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">call</span>
                                        {{ $doctor->phone }}
                                    </a>
                                @endif
                            </div>
                            <div class="bg-[#f2f3ff] p-4 rounded-xl space-y-1">
                                <p class="text-[11px] uppercase font-bold text-[#131b2e] tracking-wider">Tarifs &amp; remboursement</p>
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-[13px] text-[#434655]">Consultation en cabinet</span>
                                    <span class="text-[14px] font-bold text-[#131b2e]">Non tarifé ici</span>
                                </div>
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-[13px] text-[#434655]">Téléconsultation</span>
                                    <span class="text-[14px] font-bold text-[#434655]">Non gérée</span>
                                </div>
                                <p class="pt-2 text-[12px] font-medium text-[#434655]">
                                    Aucun tarif ni organisme de mutuelle n’est stocké. Le règlement se fait au cabinet.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <div class="relative w-full h-64 rounded-xl overflow-hidden bg-[#283044] p-4"
                                 @if($doctor->address) data-location="{{ $doctor->address }}" @endif>
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_30%,#4edea3_0,transparent_28%),radial-gradient(circle_at_80%_70%,#b4c5ff_0,transparent_32%)] opacity-30"></div>
                                <div class="absolute inset-0 bg-[#004ac6]/10"></div>
                                <div class="absolute top-4 left-4 bg-white/90 px-2 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5 text-[11px] font-semibold">
                                    <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">pin_drop</span>
                                    Cabinet
                                </div>
                                @if($maps)
                                    <a href="{{ $maps }}" target="_blank" rel="noopener noreferrer"
                                       class="absolute bottom-4 right-4 bg-[#004ac6] text-white px-2 py-1.5 rounded-lg shadow-md inline-flex items-center gap-1 text-[11px] font-bold">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">directions</span>
                                        Itinéraire
                                    </a>
                                @endif
                            </div>
                            @if($doctor->address)
                                <p class="text-[11px] text-[#434655] text-center break-words">{{ $doctor->address }}</p>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-xl p-6 shadow-sm space-y-4 scroll-mt-24" id="reviews">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="font-display text-[20px] leading-7 font-semibold tracking-tight text-[#131b2e] flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#004ac6]" aria-hidden="true">rate_review</span>
                                Avis patients
                            </h2>
                            <p class="text-[13px] leading-5 text-[#434655]">
                                Retours authentiques issus des rendez-vous SantéConnect — aucun avis n’est encore en base.
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-[28px] leading-9 font-bold text-[#131b2e]">—</p>
                            <p class="text-[12px] text-[#434655]">Aucune note</p>
                        </div>
                    </div>
                    <p class="text-sm text-[#434655]">
                        Aucun avis patient n’est encore enregistré. SantéConnect n’affiche pas de notes fictives.
                    </p>
                </section>
            </div>

            <aside class="lg:col-span-4 space-y-4 min-w-0 lg:sticky lg:top-24">
                <section class="bg-white rounded-xl p-4 shadow-md space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded-full bg-[#89f5e7] text-[#00201d] text-[11px] font-bold tracking-wide">RÉSERVATION EN LIGNE</span>
                            <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">calendar_month</span>
                        </div>
                        <h2 class="font-display text-[20px] leading-7 font-bold text-[#131b2e] mt-1.5">Prendre rendez-vous</h2>
                        <p class="text-[13px] leading-5 text-[#434655] mt-1">
                            Choisissez un créneau réel. La demande restera en attente jusqu’à confirmation du médecin.
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#434655]">1. Type de consultation</p>
                        <div class="grid grid-cols-2 p-1 bg-[#f2f3ff] rounded-lg gap-1">
                            <div class="flex flex-col items-center justify-center py-2 px-1 rounded-md bg-white shadow-sm text-[#004ac6] text-[14px] font-semibold">
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">apartment</span>
                                    En cabinet
                                </span>
                                <span class="text-[11px] font-medium text-[#434655] mt-0.5">Seul mode géré</span>
                            </div>
                            <div class="flex flex-col items-center justify-center py-2 px-1 rounded-md text-[#737686] text-[14px] font-semibold opacity-70" aria-disabled="true">
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">videocam</span>
                                    Téléconsult
                                </span>
                                <span class="text-[11px] font-medium mt-0.5">Non gérée</span>
                            </div>
                        </div>
                    </div>

                    @if($firstBookable)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#434655]">2. Choisir une date</p>
                                <span class="text-[11px] font-bold text-[#004ac6]">{{ $agendaMonth }}</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1.5 text-center">
                                @foreach($widgetDays as $date => $slots)
                                    @php $d = \Carbon\Carbon::parse($date); @endphp
                                    <button type="button"
                                            class="p-2 rounded-lg flex flex-col items-center transition-colors"
                                            :class="pickerDay === '{{ $date }}' ? 'bg-[#004ac6] text-white shadow-md' : 'bg-[#f2f3ff] text-[#434655]'"
                                            @click="pickerDay = '{{ $date }}'; day = '{{ $date }}'">
                                        <span class="text-[11px] uppercase">{{ $d->translatedFormat('D') }}</span>
                                        <span class="font-display text-[16px] font-bold">{{ $d->format('j') }}</span>
                                        <span class="w-1.5 h-1.5 rounded-full mt-1" :class="pickerDay === '{{ $date }}' ? 'bg-white' : 'bg-[#006a61]'"></span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#434655]">3. Choisir un horaire</p>
                                <span class="text-[11px] font-medium text-[#006242]" x-text="(Object.values(meta).filter(item => item.day === pickerDay).length) + ' ouvert(s)'"></span>
                            </div>
                            @foreach($widgetDays as $date => $slots)
                                @php
                                    $morning = $slots->filter(fn ($s) => (int) substr($s->start_time, 0, 2) < 12);
                                    $afternoon = $slots->filter(fn ($s) => (int) substr($s->start_time, 0, 2) >= 12);
                                @endphp
                                <div x-show="pickerDay === '{{ $date }}'" x-cloak class="space-y-2">
                                    @if($morning->isNotEmpty())
                                        <div>
                                            <span class="text-[11px] text-[#434655] font-medium inline-flex items-center gap-1 mb-1">
                                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">wb_sunny</span> Matin
                                            </span>
                                            <div class="grid grid-cols-3 gap-1.5">
                                                @foreach($morning->take(6) as $slot)
                                                    <button type="button"
                                                            class="py-2 px-1 text-center rounded-lg text-[12px] font-medium"
                                                            :class="selectedId === {{ $slot->id }} ? 'bg-[#004ac6] text-white font-bold shadow-sm' : 'bg-[#f2f3ff] text-[#131b2e]'"
                                                            @click="selectedId = {{ $slot->id }}">
                                                        {{ substr($slot->start_time, 0, 5) }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if($afternoon->isNotEmpty())
                                        <div>
                                            <span class="text-[11px] text-[#434655] font-medium inline-flex items-center gap-1 mb-1">
                                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">sunny</span> Après-midi
                                            </span>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                @foreach($afternoon->take(6) as $slot)
                                                    <button type="button"
                                                            class="py-2 px-1 text-center rounded-lg text-[12px] font-medium"
                                                            :class="selectedId === {{ $slot->id }} ? 'bg-[#004ac6] text-white font-bold shadow-sm' : 'bg-[#f2f3ff] text-[#131b2e]'"
                                                            @click="selectedId = {{ $slot->id }}">
                                                        {{ substr($slot->start_time, 0, 5) }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="bg-[#dbe1ff] p-2 rounded-xl space-y-1">
                            <div class="flex items-center gap-1.5 text-[#004ac6] text-[12px] font-bold">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">event_available</span>
                                <span x-text="selectedLabel() ? ('Sélection : ' + selectedLabel() + ' (cabinet)') : {{ \Illuminate\Support\Js::from('Prochain : '.$firstBookable->date->format('d/m/Y').' · '.substr($firstBookable->start_time, 0, 5)) }}"></span>
                            </div>
                            @if($doctor->address)
                                <p class="text-[13px] text-[#003ea8] break-words">{{ $doctor->address }}</p>
                            @endif
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <a href="{{ route('patient.appointments.create', ['availability_id' => $firstBookable->id]) }}"
                               :href="checkoutUrl()"
                               class="w-full h-11 bg-[#004ac6] hover:bg-[#2563eb] text-white rounded-lg text-[14px] font-semibold shadow-md inline-flex items-center justify-center gap-2">
                                Prendre rendez-vous
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                            </a>
                            <noscript>
                                <ul class="space-y-1.5 pt-1">
                                    @foreach($widgetSlots as $slot)
                                        <li>
                                            <a href="{{ route('patient.appointments.create', ['availability_id' => $slot->id]) }}"
                                               class="block text-[13px] text-[#004ac6] hover:underline">
                                                {{ $slot->date->format('d/m/Y') }} · {{ substr($slot->start_time, 0, 5) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </noscript>
                            <button type="button"
                                    class="w-full h-10 bg-[#f2f3ff] text-[#434655] hover:text-[#131b2e] rounded-lg text-[12px] font-semibold inline-flex items-center justify-center gap-1.5"
                                    @click="Alpine.$data(document.body).showToast('Messagerie pré-réservation non configurée.')">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chat</span>
                                Poser une question
                            </button>
                        </div>
                    @else
                        <a href="{{ route('patient.doctors.index') }}" class="w-full h-11 bg-[#f2f3ff] text-[#131b2e] rounded-lg text-sm font-semibold inline-flex items-center justify-center">
                            Consulter d'autres médecins
                        </a>
                    @endif

                    <div class="space-y-2 text-[12px] text-[#434655]">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#006242] text-[18px]" aria-hidden="true">check_circle</span>
                            <span>Annulation possible si le rendez-vous est futur et encore pending ou accepted.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">verified_user</span>
                            <span>Profil présent dans l’annuaire validé.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#006a61] text-[18px]" aria-hidden="true">lock</span>
                            <span>Données de rendez-vous conservées par l’application existante.</span>
                        </div>
                    </div>

                    <button type="button"
                            class="w-full h-10 text-[#434655] hover:bg-[#f2f3ff] rounded-lg text-[12px] font-semibold inline-flex items-center justify-center gap-1.5"
                            @click="saved = !saved; Alpine.$data(document.body).showToast(saved ? 'Médecin enregistré dans vos favoris (démo).' : 'Favori retiré (démo).')">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true" x-text="saved ? 'favorite' : 'favorite_border'">favorite_border</span>
                        <span x-text="saved ? 'Enregistré (démo)' : 'Enregistrer le médecin'"></span>
                    </button>

                    <div class="p-2 rounded-lg bg-[#ffdad6] text-[#93000a] text-[12px] font-medium flex items-start gap-2">
                        <span class="material-symbols-outlined text-[18px] text-[#ba1a1a] shrink-0 mt-0.5" aria-hidden="true">e911_emergency</span>
                        <span>Pour une douleur thoracique aiguë ou une urgence, appelez le SAMU 15 ou le 112.</span>
                    </div>
                </section>

                <section class="bg-white p-4 rounded-xl shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#86f2e4] text-[#006f66] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">support_agent</span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[14px] font-semibold text-[#131b2e]">Besoin d’aide pour réserver ?</h3>
                        <p class="text-[13px] text-[#434655] break-words">
                            @if($doctor->phone)
                                Contactez le cabinet au {{ $doctor->phone }}.
                            @else
                                Aucun numéro d’assistance n’est enregistré pour ce profil.
                            @endif
                        </p>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    @if($firstBookable)
        <div class="lg:hidden sticky bottom-3 z-30 px-4 pb-3">
            <a href="{{ route('patient.appointments.create', ['availability_id' => $firstBookable->id]) }}"
               :href="checkoutUrl()"
               class="w-full h-12 bg-[#004ac6] text-white rounded-xl text-sm font-semibold shadow-xl inline-flex items-center justify-center">
                Prendre rendez-vous
            </a>
        </div>
    @endif
</div>
<style>
    html, body { overflow-x: hidden; }
</style>
@endsection
