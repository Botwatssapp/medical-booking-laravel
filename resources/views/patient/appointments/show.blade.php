@php
    $justBooked = session('success') === 'Demande de rendez-vous envoyée. Elle est en attente de confirmation.';
    $patientUser = $appointment->patient ?? Auth::user();
    $patientName = $patientUser->name;
    $firstName = explode(' ', trim($patientName))[0] ?: $patientName;
    $canEditNotes = in_array($appointment->status, ['pending', 'accepted'], true)
        && $appointment->appointment_date->isFuture();
    $start = $appointment->availability
        ? $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->start_time)
        : $appointment->appointment_date;
    $end = $appointment->availability
        ? $appointment->appointment_date->copy()->setTimeFromTimeString($appointment->availability->end_time)
        : $appointment->appointment_date->copy()->addHour();
    $timeLabel = $appointment->availability
        ? substr($appointment->availability->start_time, 0, 5).' – '.substr($appointment->availability->end_time, 0, 5)
        : $appointment->appointment_date->format('H:i');
    $ics = [
        'id' => $appointment->id,
        'stamp' => now()->format('Ymd\THis'),
        'start' => $start->format('Ymd\THis'),
        'end' => $end->format('Ymd\THis'),
        'summary' => 'Rendez-vous Dr. '.$appointment->doctor->user->name,
        'description' => 'SantéConnect #'.$appointment->id.' — '.$appointment->doctor->speciality->name,
        'location' => $appointment->doctor->address ?? '',
    ];
    $gcal = 'https://calendar.google.com/calendar/render?'.http_build_query([
        'action' => 'TEMPLATE',
        'text' => $ics['summary'],
        'dates' => $ics['start'].'/'.$ics['end'],
        'details' => $ics['description'],
        'location' => $ics['location'],
    ]);
    $maps = $appointment->doctor->address
        ? 'https://www.google.com/maps/search/?'.http_build_query(['api' => 1, 'query' => $appointment->doctor->address])
        : null;
@endphp

@extends('layouts.app')
@section('title', $justBooked ? 'Confirmation du rendez-vous' : 'Détail du rendez-vous')
@if($justBooked)
    @section('suppress-global-flash', true)
@endif

@section('content')
<div class="bg-[#faf8ff] min-h-screen overflow-x-hidden">
    <div class="sc-page sc-stack">

        @if($justBooked)
            <section class="w-full bg-white rounded-xl p-4 sm:p-6 shadow-sm print:hidden">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#007d55] text-white shrink-0">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">done_all</span>
                        </span>
                        <div>
                            <p class="text-[11px] uppercase tracking-wider text-[#737686] font-semibold">Parcours de réservation</p>
                            <p class="text-base font-semibold text-[#131b2e]">Étape 4 sur 4 : Terminé</p>
                        </div>
                    </div>
                    <ol class="grid grid-cols-2 sm:grid-cols-4 gap-2 w-full md:w-auto md:min-w-[620px]">
                        @foreach(['Consultation', 'Motif', 'Paiement', 'Pass confirmé'] as $i => $label)
                            <li @class([
                                'flex items-center gap-2 p-2 rounded-lg',
                                'bg-[#dbe1ff] text-[#00174b]' => $i === 3,
                                'bg-[#f2f3ff] text-[#434655]' => $i !== 3,
                            ])>
                                <span class="material-symbols-outlined text-[18px] {{ $i === 3 ? 'text-[#004ac6]' : 'text-[#006242]' }}" aria-hidden="true">
                                    {{ $i === 3 ? 'verified' : 'check_circle' }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold {{ $i === 3 ? 'text-[#004ac6]' : 'text-[#006242]' }}">Étape {{ $i + 1 }}</p>
                                    <p class="text-[12px] truncate {{ $i === 3 ? 'font-semibold' : '' }}">{{ $label }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        <section class="relative w-full rounded-2xl bg-gradient-to-r from-[#f2f3ff] via-white to-[#dae2fd] p-5 sm:p-8 overflow-hidden shadow-sm">
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-[#dbe1ff]/40 blur-3xl pointer-events-none"></div>
            <div class="absolute right-32 -bottom-24 w-72 h-72 rounded-full bg-[#89f5e7]/40 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-4 max-w-3xl">
                    <div class="relative shrink-0 mt-1">
                        <div class="w-16 h-16 rounded-2xl bg-[#007d55] text-white flex items-center justify-center shadow-lg shadow-[#006242]/20">
                            <span class="material-symbols-outlined text-[36px]" aria-hidden="true">task_alt</span>
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#86f2e4] text-[#006f66] flex items-center justify-center text-[11px] font-bold" aria-hidden="true">✓</span>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-[#6ffbbe] text-[#002113] text-[11px] font-semibold tracking-wide">
                                {{ $justBooked ? 'Demande envoyée' : 'Pass du rendez-vous' }}
                            </span>
                            @include('patient.partials.status-badge', ['appointment' => $appointment])
                            <button type="button"
                                    class="px-2.5 py-0.5 rounded-full bg-[#eaedff] text-[#434655] text-[11px] font-medium hover:underline"
                                    @click="copyText({{ \Illuminate\Support\Js::from((string) $appointment->id) }}, 'Référence copiée.')">
                                Réf. #{{ $appointment->id }}
                            </button>
                        </div>
                        <h1 class="font-display mt-2 text-[32px] sm:text-[40px] leading-10 sm:leading-[48px] font-bold text-[#131b2e] tracking-tight">
                            @if($justBooked)
                                C’est enregistré, {{ $firstName }} !
                            @else
                                Détail du rendez-vous
                            @endif
                        </h1>
                        <p class="mt-2 text-[15px] leading-6 text-[#434655] max-w-2xl">
                            @if($justBooked)
                                Demande de rendez-vous envoyée. Elle est en attente de confirmation.
                            @else
                                Pass et informations de votre consultation avec Dr. {{ $appointment->doctor->user->name }}.
                            @endif
                            @if($patientUser->email)
                                Un compte est associé à
                                <span class="font-semibold text-[#131b2e]">{{ $patientUser->email }}</span>.
                            @endif
                            Aucun SMS ni pass Wallet n’a été envoyé automatiquement.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row lg:flex-col gap-2 shrink-0 print:hidden">
                    <div class="flex items-center gap-2">
                        <a href="{{ $gcal }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white hover:bg-[#eaedff] text-[#131b2e] text-xs font-semibold shadow-sm">
                            <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">event_available</span>
                            Google Calendar
                        </a>
                        <button type="button"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white hover:bg-[#eaedff] text-[#131b2e] text-xs font-semibold shadow-sm"
                                @click="downloadIcs({{ \Illuminate\Support\Js::from($ics) }})">
                            <span class="material-symbols-outlined text-[#434655] text-[18px]" aria-hidden="true">calendar_month</span>
                            Apple iCal
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white text-xs font-semibold shadow-sm"
                                @click="printPass()">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">print</span>
                            Imprimer le pass
                        </button>
                        <button type="button"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white hover:bg-[#eaedff] text-[#131b2e] text-xs font-semibold shadow-sm"
                                @click="showToast('Aucun reçu PDF n’est généré — fonctionnalité non configurée.')">
                            <span class="material-symbols-outlined text-[#434655] text-[18px]" aria-hidden="true">download</span>
                            Reçu PDF
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
            <div class="lg:col-span-7 flex flex-col gap-6">
                <x-ui.digital-pass :appointment="$appointment" />

                <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="material-symbols-outlined text-[#004ac6]" aria-hidden="true">notes</span>
                        <h2 class="text-base font-bold text-[#131b2e]">Notes de consultation</h2>
                    </div>
                    <p class="text-sm text-[#131b2e] whitespace-pre-line">{{ $appointment->notes ?: 'Aucune note.' }}</p>
                    @if($canEditNotes)
                        <a href="{{ route('patient.appointments.edit', $appointment) }}"
                           class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-[#004ac6] hover:underline print:hidden">
                            Modifier les notes
                        </a>
                    @endif
                </section>

                <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm print:hidden">
                    <div class="flex items-center justify-between mb-4 gap-3">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[#004ac6] text-[24px]" aria-hidden="true">checklist</span>
                            <h2 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Préparer la consultation</h2>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#eaedff] text-[#434655] text-[11px] font-semibold">Conseils</span>
                    </div>
                    <p class="text-[13px] text-[#434655] mb-4">Aucune pièce, mutuelle ou fichier n’est vérifié par SantéConnect.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-[#f2f3ff]">
                            <span class="material-symbols-outlined text-[#006242] text-[20px] shrink-0 mt-0.5" aria-hidden="true" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <div>
                                <p class="text-sm font-semibold text-[#131b2e]">Pièce d’identité</p>
                                <p class="text-[13px] text-[#434655]">Prévoyez une pièce officielle si le cabinet la demande à l’accueil.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-[#f2f3ff]">
                            <span class="material-symbols-outlined text-[#006242] text-[20px] shrink-0 mt-0.5" aria-hidden="true" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <div>
                                <p class="text-sm font-semibold text-[#131b2e]">Carte mutuelle</p>
                                <p class="text-[13px] text-[#434655]">Aucune affiliation n’est enregistrée ici. Apportez votre carte si vous en avez une.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-[#f2f3ff]">
                            <span class="material-symbols-outlined text-[#006242] text-[20px] shrink-0 mt-0.5" aria-hidden="true" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <div>
                                <p class="text-sm font-semibold text-[#131b2e]">Documents médicaux</p>
                                <p class="text-[13px] text-[#434655]">Aucun dossier n’est téléversé dans l’application. Apportez vos documents au cabinet.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-[#eaedff]">
                            <span class="material-symbols-outlined text-[#006a61] text-[20px] shrink-0 mt-0.5" aria-hidden="true">info</span>
                            <div>
                                <p class="text-sm font-semibold text-[#131b2e]">Consigne générale</p>
                                <p class="text-[13px] text-[#434655]">Aucun jeûne n’est imposé par la plateforme. Suivez les consignes de votre médecin.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm print:hidden">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="material-symbols-outlined text-[#006a61]" aria-hidden="true">pin_drop</span>
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-[#131b2e]">Lieu &amp; accès</h2>
                                <p class="text-sm text-[#434655] break-words">
                                    {{ $appointment->doctor->address ?: 'Adresse du cabinet non renseignée.' }}
                                </p>
                            </div>
                        </div>
                        @if($appointment->doctor->phone)
                            <a href="tel:{{ $appointment->doctor->phone }}"
                               class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-xs font-semibold shrink-0">
                                <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">call</span>
                                {{ $appointment->doctor->phone }}
                            </a>
                        @endif
                    </div>
                    @if($maps)
                        <div class="relative w-full h-56 rounded-xl overflow-hidden bg-[#283044] p-4 flex flex-col justify-end"
                             @if($appointment->doctor->address) data-location="{{ $appointment->doctor->address }}" @endif>
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_30%,#4edea3_0,transparent_28%),radial-gradient(circle_at_80%_70%,#b4c5ff_0,transparent_32%)] opacity-30"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-[#283044]/90 via-[#283044]/30 to-transparent"></div>
                            <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-[#eef0ff]">
                                <p class="font-display text-[16px] leading-6 font-bold break-words">{{ $appointment->doctor->address }}</p>
                                <a href="{{ $maps }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-[#004ac6] text-white text-xs font-semibold shrink-0">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">directions</span>
                                    Itinéraire GPS
                                </a>
                            </div>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-[#f2f3ff]">
                            <div class="w-9 h-9 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#004ac6] shrink-0">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">map</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[12px] font-semibold text-[#131b2e]">Adresse du cabinet</p>
                                <p class="text-[12px] text-[#434655] break-words">{{ $appointment->doctor->address ?: 'Non renseignée' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-[#f2f3ff]">
                            <div class="w-9 h-9 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#006a61] shrink-0">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">{{ $appointment->doctor->phone ? 'call' : 'domain' }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[12px] font-semibold text-[#131b2e]">{{ $appointment->doctor->phone ? 'Téléphone du cabinet' : 'Consultation en cabinet' }}</p>
                                <p class="text-[12px] text-[#434655] break-words">
                                    {{ $appointment->doctor->phone ?: 'Présentiel — aucun autre mode géré.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="lg:col-span-5 flex flex-col gap-6 print:hidden">
                <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                    <div class="flex items-center justify-between pb-1">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-[#737686]">Règlement</p>
                            <h2 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Paiement &amp; couverture</h2>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#eaedff] text-[#434655] text-[11px] font-semibold">Hors plateforme</span>
                    </div>
                    <div class="mt-4 space-y-2 bg-[#f2f3ff] p-4 rounded-xl text-[13px]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#434655]">Acte {{ $appointment->doctor->speciality->name }}</span>
                            <span class="font-semibold text-[#131b2e]">Non tarifé ici</span>
                        </div>
                        <div class="flex items-center justify-between text-[#006242]">
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">verified_user</span>
                                Mutuelle
                            </span>
                            <span class="font-semibold">Non gérée</span>
                        </div>
                        <div class="h-px w-full bg-[#dae2fd]"></div>
                        <div class="flex items-center justify-between font-display text-[16px] leading-6">
                            <span class="font-semibold text-[#131b2e]">Montant encaissé ici</span>
                            <span class="font-bold text-[#004ac6]">—</span>
                        </div>
                    </div>
                    <div class="mt-3 p-4 rounded-xl bg-[#eaedff] flex items-start gap-3">
                        <span class="material-symbols-outlined text-[#004ac6] text-[20px] shrink-0 mt-0.5" aria-hidden="true">description</span>
                        <div class="min-w-0">
                            <p class="text-[12px] font-semibold text-[#131b2e]">Feuille de soins / quittance</p>
                            <p class="text-[13px] text-[#434655]">Aucune feuille de soins électronique ni quittance n’est générée par SantéConnect. Le règlement se fait hors plateforme, selon le cabinet.</p>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                    <h2 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Gestion du rendez-vous</h2>
                    <p class="text-[13px] text-[#434655] mt-1">
                        Annulation possible tant que le rendez-vous est futur et encore en attente ou accepté.
                        Le report n’est pas une action séparée : il faut annuler puis réserver un autre créneau.
                    </p>
                    <div class="mt-4 flex flex-col gap-2">
                        <a href="{{ route('patient.doctors.show', $appointment->doctor) }}"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-[#eaedff] hover:bg-[#e2e7ff] text-[#131b2e] text-sm font-semibold">
                            <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">stethoscope</span>
                            Voir le médecin
                        </a>
                        <button type="button"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-[#eaedff] hover:bg-[#e2e7ff] text-[#131b2e] text-sm font-semibold"
                                @click="showToast('Le report n’est pas disponible. Annulez ce rendez-vous puis réservez un autre créneau.')">
                            <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">save_as</span>
                            Reprogrammer (non disponible)
                        </button>
                        <button type="button"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-[#eaedff] hover:bg-[#e2e7ff] text-[#131b2e] text-sm font-semibold"
                                @click="showToast('Messagerie secrétariat non configurée.')">
                            <span class="material-symbols-outlined text-[#006a61] text-[18px]" aria-hidden="true">chat</span>
                            Contacter le secrétariat
                        </button>
                        @include('patient.partials.cancel-form', [
                            'appointment' => $appointment,
                            'label' => 'Annuler ce rendez-vous',
                            'buttonClass' => 'w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-[#ffdad6]/70 hover:bg-[#ffdad6] text-[#93000a] text-sm font-semibold',
                        ])
                    </div>
                </section>

                <section class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm">
                    <h2 class="text-base font-bold text-[#131b2e]">Portefeuille</h2>
                    <p class="text-sm text-[#434655] mt-1">Aperçu visuel uniquement — aucune intégration Wallet.</p>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <button type="button" class="inline-flex items-center justify-center gap-2 min-h-11 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#eaedff] hover:bg-[#e2e7ff]" @click="simulateWallet('apple')">Apple Wallet</button>
                        <button type="button" class="inline-flex items-center justify-center gap-2 min-h-11 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#eaedff] hover:bg-[#e2e7ff]" @click="simulateWallet('google')">Google Wallet</button>
                    </div>
                    <x-ui.modal name="wallet" title="Aperçu du pass">
                        <x-ui.demo-notice class="mb-4">Aucun pass Apple ou Google n’est généré. Ceci est une représentation visuelle.</x-ui.demo-notice>
                        <div class="sc-wallet bg-gradient-to-br from-black to-zinc-700" x-show="wallet === 'apple'">
                            <p class="text-xs uppercase tracking-widest text-white/60">Apple Wallet · aperçu</p>
                            <p class="text-lg font-bold">Dr. {{ $appointment->doctor->user->name }}</p>
                            <p class="text-sm text-white/70">{{ $appointment->appointment_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="sc-wallet bg-gradient-to-br from-[#1a73e8] to-[#0b3d91]" x-show="wallet === 'google'">
                            <p class="text-xs uppercase tracking-widest text-white/70">Google Wallet · aperçu</p>
                            <p class="text-lg font-bold">Dr. {{ $appointment->doctor->user->name }}</p>
                            <p class="text-sm text-white/80">{{ $appointment->appointment_date->format('d/m/Y') }}</p>
                        </div>
                    </x-ui.modal>
                </section>

                <section class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center gap-3 text-[#ba1a1a]">
                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">emergency</span>
                        <h2 class="text-base font-bold text-[#131b2e]">Avis d’urgence</h2>
                    </div>
                    <p class="mt-2 text-sm text-[#434655]">
                        SantéConnect ne remplace pas un service d’urgence. En cas de douleur thoracique, essoufflement soudain ou détresse vitale, n’attendez pas ce rendez-vous.
                    </p>
                    <div class="mt-3 p-3 bg-[#ffdad6] rounded-lg flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-[#ba1a1a] text-[20px]" aria-hidden="true">e911_emergency</span>
                            <span class="text-xs font-bold text-[#93000a]">SAMU immédiat</span>
                        </div>
                        <a href="tel:15" class="px-3 py-1 bg-[#ba1a1a] text-white rounded-md text-xs font-bold shrink-0">Appeler le 15 / 112</a>
                    </div>
                </section>
            </aside>
        </div>

        <div class="w-full bg-white rounded-2xl p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 print:hidden">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-[#006242] animate-pulse"></span>
                <p class="text-[13px] text-[#434655]">Référence #{{ $appointment->id }} — jeton de rendez-vous conservé dans votre espace patient.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <button type="button"
                        class="w-full sm:w-auto text-center px-6 py-2.5 rounded-lg bg-[#eaedff] hover:bg-[#e2e7ff] text-[#131b2e] text-sm font-semibold"
                        @click="simulateFamilyBooking()">
                    Réserver pour un proche
                </button>
                <a href="{{ route('patient.dashboard') }}"
                   class="w-full sm:w-auto text-center px-8 py-2.5 rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white text-sm font-semibold shadow-sm inline-flex items-center justify-center gap-2">
                    Tableau de bord patient
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    html, body { overflow-x: hidden; }
    @media print {
        nav, footer, .print\\:hidden { display: none !important; }
        body { background: white !important; }
        main { padding: 0 !important; }
    }
</style>
@endsection
