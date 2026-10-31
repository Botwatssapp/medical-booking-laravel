@extends('layouts.app')
@section('title', 'Confirmer le rendez-vous')

@section('content')
<div class="min-h-screen bg-[#faf8ff] text-[#131b2e]">

    @if(!$availability)
        <div class="sc-page">
            <div class="bg-white rounded-2xl shadow-sm p-10 text-center">
                <span class="material-symbols-outlined text-5xl text-[#c3c6d7] block mb-3" aria-hidden="true">event_busy</span>
                <p class="font-medium text-[#131b2e]">Aucun créneau disponible n’a été sélectionné.</p>
                <p class="text-sm text-[#434655] mt-1">Le créneau a peut-être déjà été réservé. Choisissez un autre horaire.</p>
                <a href="{{ route('patient.doctors.index') }}" class="mt-4 inline-block text-[#004ac6] hover:underline text-sm">Choisir un médecin</a>
            </div>
        </div>
    @else
        @php
            $doctor = $availability->doctor;
            $doctorUser = $doctor->user;
            $photo = $doctorUser->profile_image_url ?? ($doctor->photo ? asset('storage/'.$doctor->photo) : null);
            $timeLabel = substr($availability->start_time, 0, 5).' – '.substr($availability->end_time, 0, 5);
            $duration = null;
            try {
                $duration = (int) \Carbon\Carbon::parse($availability->start_time)->diffInMinutes(\Carbon\Carbon::parse($availability->end_time));
            } catch (\Throwable) {
                $duration = null;
            }
            $patient = Auth::user();
            $initials = collect(preg_split('/\s+/', trim($patient->name)))->filter()->map(fn ($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('');
            $startStep = $errors->has('notes') ? 3 : ($errors->any() ? 1 : 1);
        @endphp

        <div class="w-full bg-[#f2f3ff] py-6 sm:py-8 shadow-sm">
            <div class="sc-page-wide">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-1.5 text-[#004ac6] text-[12px] font-semibold uppercase tracking-wider mb-1">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">verified_user</span>
                            <span>Confirmation clinique</span>
                        </div>
                        <h1 class="font-display text-[24px] sm:text-[28px] leading-8 sm:leading-9 font-bold text-[#131b2e]">Confirmation sécurisée du rendez-vous</h1>
                        <p class="mt-1 text-sm text-[#434655]">
                            Votre demande sera transmise à Dr. {{ $doctorUser->name }}. Le rendez-vous restera
                            <strong class="text-[#131b2e]">en attente</strong> jusqu’à confirmation.
                        </p>
                    </div>
                    <div class="flex flex-col items-stretch sm:items-end gap-2">
                        <div class="inline-flex items-center gap-2 bg-white px-4 py-1.5 rounded-full shadow-sm"
                             x-data="{
                                left: 892,
                                label() {
                                    const m = Math.floor(this.left / 60);
                                    const s = this.left % 60;
                                    return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                                }
                             }"
                             x-init="setInterval(() => { if (left > 0) left-- }, 1000)">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#007d55] animate-pulse"></span>
                            <span class="text-[11px] text-[#434655]">
                                Aperçu minuteur :
                                <span class="font-bold text-[#004ac6]" x-text="label()">14:52</span>
                            </span>
                        </div>
                        <p class="text-[11px] text-[#434655] text-right max-w-xs">Le créneau n’est pas bloqué tant que la demande n’est pas envoyée.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="sc-page-wide py-8 sm:py-10" x-data="scBooking({{ $startStep }})">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4 mb-8">
                @foreach([
                    1 => 'Détails de consultation',
                    2 => 'Motif',
                    3 => 'Couverture & paiement',
                    4 => 'Garantie instantanée',
                ] as $n => $label)
                    <div class="flex items-center gap-2 sm:gap-3 p-2 sm:p-4 rounded-xl shadow-sm transition-all"
                         :class="step === {{ $n }} ? 'bg-[#2563eb] text-white shadow-md' : (step > {{ $n }} ? 'bg-white' : 'bg-white/70 opacity-60')">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm font-bold"
                             :class="step === {{ $n }} ? 'bg-white text-[#004ac6]' : (step > {{ $n }} ? 'bg-[#006242] text-white' : 'bg-[#dae2fd] text-[#434655]')">
                            <span x-show="step > {{ $n }}" class="material-symbols-outlined text-[18px]" aria-hidden="true">check</span>
                            <span x-show="step <= {{ $n }}">{{ $n }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase" :class="step === {{ $n }} ? 'text-white/80' : (step > {{ $n }} ? 'text-[#006242]' : 'text-[#434655]')">
                                <span x-show="step === {{ $n }}">Étape active</span>
                                <span x-show="step > {{ $n }}">Étape {{ $n }}</span>
                                <span x-show="step < {{ $n }}">À venir</span>
                            </p>
                            <p class="text-sm font-semibold truncate" :class="step === {{ $n }} ? 'text-white font-bold' : 'text-[#131b2e]'">{{ $label }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('patient.appointments.store') }}" @submit="submitting = true">
                @csrf
                <input type="hidden" name="doctor_id" value="{{ $availability->doctor_id }}">
                <input type="hidden" name="availability_id" value="{{ $availability->id }}">
                <noscript>
                    <div class="mb-6 p-4 rounded-xl bg-[#eef2ff] text-sm text-[#131b2e]">
                        JavaScript est désactivé. Vérifiez le créneau ci-dessous, puis envoyez la demande.
                    </div>
                </noscript>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-8 flex flex-col gap-6 min-w-0">

                        <div x-show="step === 1" class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm space-y-5">
                            <span class="inline-flex text-[11px] font-bold uppercase tracking-wider text-[#00174b] bg-[#dbe1ff] px-2 py-0.5 rounded-full">Étape 1 / 4</span>
                            <h2 class="font-display text-[28px] leading-9 font-bold text-[#131b2e]">Détails de consultation</h2>
                            <p class="text-[15px] text-[#434655]">Vérifiez le médecin et le créneau sélectionnés. Aucun paiement n’est requis pour envoyer la demande.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-4 rounded-xl bg-[#e2e7ff]/60">
                                    <p class="text-[11px] uppercase tracking-wider text-[#434655] font-bold">Médecin</p>
                                    <p class="mt-1 font-bold text-[#131b2e]">Dr. {{ $doctorUser->name }}</p>
                                    <p class="text-sm text-[#004ac6]">{{ $doctor->speciality->name }}</p>
                                </div>
                                <div class="p-4 rounded-xl bg-[#e2e7ff]/60">
                                    <p class="text-[11px] uppercase tracking-wider text-[#434655] font-bold">Créneau</p>
                                    <p class="mt-1 font-bold text-[#131b2e]">{{ $availability->date->translatedFormat('l j F Y') }}</p>
                                    <p class="text-sm text-[#004ac6]">{{ $timeLabel }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                                <a href="{{ route('patient.doctors.show', $doctor) }}" class="w-full sm:w-auto px-6 py-3 rounded-lg bg-[#f2f3ff] hover:bg-[#eaedff] text-[#131b2e] text-sm font-semibold inline-flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                                    Retour aux disponibilités
                                </a>
                                <button type="button" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white text-sm font-bold inline-flex items-center justify-center gap-1 shadow-md" @click="next()">
                                    Continuer vers le motif
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                </button>
                            </div>
                        </div>

                        <div x-show="step === 2" x-cloak class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm space-y-5">
                            <span class="inline-flex text-[11px] font-bold uppercase tracking-wider text-[#00174b] bg-[#dbe1ff] px-2 py-0.5 rounded-full">Étape 2 / 4</span>
                            <h2 class="font-display text-[28px] leading-9 font-bold text-[#131b2e]">Motif et documents</h2>
                            <p class="text-[15px] text-[#434655]">Préparez le motif de consultation. Le champ notes se trouve à l’étape suivante, dans la zone documents. Aucun fichier n’est enregistré.</p>
                            <div class="p-8 rounded-xl bg-[#f2f3ff] text-center flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-full bg-[#dae2fd] text-[#004ac6] flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">cloud_upload</span>
                                </div>
                                <p class="font-semibold text-[#131b2e]">Dépôt de documents — aperçu visuel</p>
                                <p class="text-sm text-[#434655]">PDF ou image : aucun fichier n’est stocké par SantéConnect.</p>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                <button type="button" class="w-full sm:w-auto px-6 py-3 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-sm font-semibold inline-flex items-center justify-center gap-1" @click="prev()">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                                    Retour à l’étape 1
                                </button>
                                <button type="button" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white text-sm font-bold inline-flex items-center justify-center gap-1 shadow-md" @click="next()">
                                    Continuer vers la couverture
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                </button>
                            </div>
                        </div>

                        <div x-show="step === 3" x-cloak class="flex flex-col gap-6">
                            <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <span class="inline-flex text-[11px] font-bold uppercase tracking-wider text-[#00174b] bg-[#dbe1ff] px-2 py-0.5 rounded-full">Étape 3 / 4</span>
                                        <h2 class="font-display text-[28px] leading-9 font-bold text-[#131b2e] mt-2">Couverture et méthode de règlement</h2>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 text-[#006242] bg-[#f2f3ff] px-2 py-1 rounded-lg shrink-0">
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">verified</span>
                                        <span class="text-[11px] font-semibold">Règlement hors plateforme</span>
                                    </div>
                                </div>
                                <p class="text-[15px] text-[#434655] mt-2">
                                    Vérifiez le bénéficiaire. SantéConnect n’enregistre aucun paiement, aucun numéro de carte et aucune affiliation mutuelle.
                                    Aucun tarif n’est stocké : le règlement se fait avec le cabinet de Dr. {{ $doctorUser->name }}.
                                </p>
                                <div class="mt-6 bg-[#f2f3ff] p-4 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="w-10 h-10 rounded-full bg-[#2563eb] text-white flex items-center justify-center shrink-0 font-bold">{{ $initials ?: 'P' }}</div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-[#131b2e] truncate">{{ $patient->name }}</span>
                                                <span class="text-[11px] bg-[#dae2fd] text-[#434655] px-1.5 py-0.5 rounded">Moi-même</span>
                                            </div>
                                            <p class="text-[13px] text-[#434655] truncate">{{ $patient->email }}</p>
                                        </div>
                                    </div>
                                    <button type="button" class="shrink-0 text-[#004ac6] hover:text-[#2563eb] text-[12px] font-semibold inline-flex items-center gap-1" @click="demoToast('Changement de bénéficiaire non disponible — aperçu visuel.')">
                                        Changer de bénéficiaire
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">swap_horiz</span>
                                    </button>
                                </div>
                            </section>

                            <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[#004ac6] text-[24px]" aria-hidden="true">health_and_safety</span>
                                        <h3 class="font-display text-[20px] leading-7 font-bold">Mutuelle et affiliation</h3>
                                    </div>
                                    <span class="text-[11px] text-[#434655]">Aperçu visuel</span>
                                </div>
                                <div class="space-y-4">
                                    <label class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl cursor-pointer transition-all"
                                           :class="cover === 'mutual' ? 'bg-[#e2e7ff]/40 shadow-sm' : 'bg-[#f2f3ff] hover:bg-[#eaedff]'">
                                        <div class="flex items-start gap-4">
                                            <input class="mt-1 w-4 h-4 accent-[#004ac6]" type="radio" @click="cover = 'mutual'" :checked="cover === 'mutual'">
                                            <div>
                                                <p class="font-bold text-[#131b2e]">Couverture mutuelle (tiers payant)</p>
                                                <p class="text-[13px] text-[#434655] mt-1">Aucun taux ni reste à charge n’est calculé : ces données n’existent pas en base.</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 sm:mt-0 text-left sm:text-right shrink-0">
                                            <p class="text-[11px] text-[#434655]">Reste à charge</p>
                                            <p class="text-[20px] font-bold text-[#434655]">Non géré</p>
                                        </div>
                                    </label>
                                    <div x-show="cover === 'mutual'" x-cloak class="bg-[#eaedff] p-4 rounded-xl space-y-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[12px] font-semibold mb-1" for="demo-mutuelle">Organisme (démo)</label>
                                                <x-ui.select id="demo-mutuelle" label="Organisme (démo)">
                                                    <option>CNSS</option>
                                                    <option>CNOPS</option>
                                                    <option>Mutuelle privée</option>
                                                </x-ui.select>
                                            </div>
                                            <div>
                                                <label class="block text-[12px] font-semibold mb-1" for="demo-affiliation">N° affiliation (démo)</label>
                                                <input id="demo-affiliation" type="text" class="w-full bg-white rounded-lg h-11 px-3 text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6]" placeholder="Non enregistré" autocomplete="off">
                                            </div>
                                        </div>
                                        <p class="text-[13px] text-[#434655] bg-white p-2 rounded-lg inline-flex items-start gap-2">
                                            <span class="material-symbols-outlined text-[#006242] text-[20px]" aria-hidden="true">info</span>
                                            Ces champs n’ont pas d’attribut name : ils ne partent pas avec la demande.
                                        </p>
                                    </div>
                                    <label class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl cursor-pointer transition-all"
                                           :class="cover === 'none' ? 'bg-[#e2e7ff]/40 shadow-sm' : 'bg-[#f2f3ff] hover:bg-[#eaedff]'">
                                        <div class="flex items-start gap-4">
                                            <input class="mt-1 w-4 h-4 accent-[#004ac6]" type="radio" @click="cover = 'none'" :checked="cover === 'none'">
                                            <div>
                                                <p class="font-bold text-[#131b2e]">Règlement personnel / feuille de soins</p>
                                                <p class="text-[13px] text-[#434655] mt-1">Mode actuellement prévu par l’application : le cabinet encaisse hors plateforme.</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 sm:mt-0 text-left sm:text-right shrink-0">
                                            <p class="text-[11px] text-[#434655]">Montant</p>
                                            <p class="text-[20px] font-bold text-[#131b2e]">Hors plateforme</p>
                                        </div>
                                    </label>
                                </div>
                            </section>

                            <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[#004ac6] text-[24px]" aria-hidden="true">meeting_room</span>
                                        <h3 class="font-display text-[20px] leading-7 font-bold">Modalité de consultation</h3>
                                    </div>
                                    <span class="text-[11px] text-[#006242] font-semibold inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-[#006242]"></span>
                                        Lieu confirmé
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <button type="button" @click="modality = 'inperson'"
                                            class="p-4 rounded-xl text-left flex items-start gap-4 transition-colors"
                                            :class="modality === 'inperson' ? 'bg-[#e2e7ff]/60' : 'bg-[#f2f3ff] hover:bg-[#eaedff]'">
                                        <div class="p-2 rounded-lg shrink-0" :class="modality === 'inperson' ? 'bg-[#004ac6] text-white' : 'bg-[#dae2fd] text-[#434655]'">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">local_hospital</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold">Présentiel</span>
                                                <span x-show="modality === 'inperson'" class="text-[11px] bg-[#004ac6] text-white px-1.5 py-0.5 rounded font-bold">Actif</span>
                                            </div>
                                            <p class="text-[13px] text-[#434655] mt-1">Mode actuellement prévu par l’application.</p>
                                            @if($doctor->address)
                                                <p class="text-[13px] text-[#434655] mt-1">{{ $doctor->address }}</p>
                                            @endif
                                        </div>
                                    </button>
                                    <button type="button" @click="modality = 'video'"
                                            class="p-4 rounded-xl text-left flex items-start gap-4 transition-colors"
                                            :class="modality === 'video' ? 'bg-[#e2e7ff]/60' : 'bg-[#f2f3ff] hover:bg-[#eaedff]'">
                                        <div class="p-2 rounded-lg bg-[#dae2fd] text-[#434655] shrink-0">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">videocam</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold">Passer en téléconsultation</p>
                                            <p class="text-[13px] text-[#434655] mt-1">Non gérée en base — choix visuel uniquement.</p>
                                        </div>
                                    </button>
                                </div>
                            </section>

                            <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[#004ac6] text-[24px]" aria-hidden="true">payments</span>
                                        <h3 class="font-display text-[20px] leading-7 font-bold">Méthode de règlement</h3>
                                    </div>
                                    <span class="text-[11px] text-[#434655] inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-[#006242]" aria-hidden="true">lock</span>
                                        Aperçu — non chiffré ici
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-6">
                                    <button type="button" @click="pay = 'card'" class="h-24 p-4 rounded-xl text-left flex flex-col justify-between transition-all" :class="pay === 'card' ? 'bg-[#e2e7ff]' : 'bg-[#f2f3ff] hover:bg-[#eaedff]'">
                                        <div class="flex items-center justify-between">
                                            <span class="material-symbols-outlined text-[#004ac6] text-[22px]" aria-hidden="true">credit_card</span>
                                            <span class="w-3.5 h-3.5 rounded-full" :class="pay === 'card' ? 'bg-[#004ac6]' : 'bg-[#dae2fd]'"></span>
                                        </div>
                                        <p class="text-sm font-bold leading-tight">Carte (CMI / Visa)</p>
                                    </button>
                                    <button type="button" @click="pay = 'wallet'" class="h-24 p-4 rounded-xl text-left flex flex-col justify-between transition-all" :class="pay === 'wallet' ? 'bg-[#e2e7ff]' : 'bg-[#f2f3ff] hover:bg-[#eaedff]'">
                                        <div class="flex items-center justify-between">
                                            <span class="material-symbols-outlined text-[#434655] text-[22px]" aria-hidden="true">phone_iphone</span>
                                            <span class="w-3.5 h-3.5 rounded-full" :class="pay === 'wallet' ? 'bg-[#004ac6]' : 'bg-[#dae2fd]'"></span>
                                        </div>
                                        <p class="text-sm font-semibold leading-tight">Apple / Google Pay</p>
                                    </button>
                                    <button type="button" @click="pay = 'desk'" class="h-24 p-4 rounded-xl text-left flex flex-col justify-between transition-all" :class="pay === 'desk' ? 'bg-[#e2e7ff]' : 'bg-[#f2f3ff] hover:bg-[#eaedff]'">
                                        <div class="flex items-center justify-between">
                                            <span class="material-symbols-outlined text-[#434655] text-[22px]" aria-hidden="true">point_of_sale</span>
                                            <span class="w-3.5 h-3.5 rounded-full" :class="pay === 'desk' ? 'bg-[#004ac6]' : 'bg-[#dae2fd]'"></span>
                                        </div>
                                        <p class="text-sm font-semibold leading-tight">Payer sur place</p>
                                    </button>
                                </div>
                                <div x-show="pay === 'card'" x-cloak class="bg-[#f2f3ff] p-4 sm:p-6 rounded-xl space-y-4">
                                    <div>
                                        <label class="block text-[12px] font-semibold mb-1" for="demo-holder">Nom du titulaire (démo)</label>
                                        <input id="demo-holder" type="text" class="w-full bg-white rounded-lg h-11 px-3 uppercase focus:outline-none focus:ring-2 focus:ring-[#004ac6]" placeholder="Nom sur la carte" autocomplete="off">
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="text-[12px] font-semibold" for="demo-card">Numéro de carte (démo)</label>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded bg-[#dae2fd] text-[11px] font-bold">CMI</span>
                                                <span class="px-1.5 py-0.5 rounded bg-[#dae2fd] text-[11px] font-bold">VISA</span>
                                                <span class="px-1.5 py-0.5 rounded bg-[#dae2fd] text-[11px] font-bold">MC</span>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input id="demo-card" type="text" inputmode="numeric" class="w-full bg-white rounded-lg h-11 px-3 pl-11 tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-[#004ac6]" placeholder="•••• •••• •••• ••••" autocomplete="off">
                                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#434655] text-[20px]" aria-hidden="true">credit_card</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[12px] font-semibold mb-1" for="demo-exp">Expiration</label>
                                            <input id="demo-exp" type="text" class="w-full bg-white rounded-lg h-11 px-3 text-center font-mono focus:outline-none focus:ring-2 focus:ring-[#004ac6]" placeholder="MM/AA" autocomplete="off">
                                        </div>
                                        <div>
                                            <label class="block text-[12px] font-semibold mb-1" for="demo-cvc">Code de sécurité</label>
                                            <input id="demo-cvc" type="password" maxlength="4" class="w-full bg-white rounded-lg h-11 px-3 text-center font-mono focus:outline-none focus:ring-2 focus:ring-[#004ac6]" placeholder="•••" autocomplete="off">
                                        </div>
                                    </div>
                                    <p class="text-[13px] text-[#434655]">Aucun de ces champs n’a d’attribut <code>name</code> : ils ne partent pas avec le formulaire.</p>
                                </div>
                            </section>

                            <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[#004ac6] text-[24px]" aria-hidden="true">folder_open</span>
                                        <h3 class="font-display text-[20px] leading-7 font-bold">Documents et motif</h3>
                                    </div>
                                    <span class="text-[11px] bg-[#eaedff] px-1.5 py-0.5 rounded text-[#434655]">Visible par le médecin</span>
                                </div>
                                <div class="space-y-4">
                                    <div class="p-6 rounded-xl bg-[#f2f3ff] text-center flex flex-col items-center gap-1">
                                        <div class="w-12 h-12 rounded-full bg-[#dae2fd] text-[#004ac6] flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">cloud_upload</span>
                                        </div>
                                        <p class="font-semibold">Ajouter un document (aperçu)</p>
                                        <p class="text-[13px] text-[#434655]">Aucun fichier n’est enregistré par l’application.</p>
                                    </div>
                                    <div>
                                        <label for="appointment-notes" class="block text-[12px] font-semibold mb-1">
                                            Motif de consultation pour Dr. {{ $doctorUser->name }}
                                            <span class="text-[#434655] font-normal">(optionnel)</span>
                                        </label>
                                        <textarea id="appointment-notes" name="notes" rows="3" maxlength="1000"
                                                  placeholder="Décrivez brièvement le motif de votre consultation"
                                                  aria-describedby="{{ $errors->has('notes') ? 'notes-error' : 'notes-help' }}"
                                                  class="w-full bg-[#f2f3ff] rounded-xl p-4 text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6] resize-none">{{ old('notes') }}</textarea>
                                        <p id="notes-help" class="text-xs text-[#434655] mt-1">Ces informations seront visibles par le médecin. C’est le seul champ libre envoyé avec la demande.</p>
                                        @error('notes')
                                            <p id="notes-error" class="text-red-700 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm space-y-6">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" class="mt-1 w-4 h-4 accent-[#004ac6]" checked>
                                    <span class="text-[13px] text-[#434655] leading-relaxed">
                                        J’accepte les conditions de prise de rendez-vous SantéConnect et la politique d’annulation :
                                        un rendez-vous futur encore <strong class="text-[#131b2e]">en attente</strong> ou <strong class="text-[#131b2e]">accepté</strong> peut être annulé.
                                        Case visuelle uniquement — non enregistrée.
                                    </span>
                                </label>
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                    <button type="button" class="w-full sm:w-auto px-6 py-3 rounded-lg bg-[#f2f3ff] hover:bg-[#eaedff] text-[#131b2e] text-sm font-semibold inline-flex items-center justify-center gap-1" @click="prev()">
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                                        Retour à l’étape 2 (motif)
                                    </button>
                                    <button type="button" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white text-sm font-bold inline-flex items-center justify-center gap-1 shadow-md" @click="next()">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock</span>
                                        Continuer vers la confirmation
                                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                    </button>
                                </div>
                            </section>
                        </div>

                        <div x-show="step === 4" x-cloak class="bg-white rounded-2xl p-5 sm:p-8 shadow-sm space-y-5">
                            <span class="inline-flex text-[11px] font-bold uppercase tracking-wider text-[#00174b] bg-[#dbe1ff] px-2 py-0.5 rounded-full">Étape 4 / 4</span>
                            <h2 class="font-display text-[28px] leading-9 font-bold text-[#131b2e]">Garantie instantanée</h2>
                            <p class="text-[15px] text-[#434655]">
                                La demande sera créée avec le statut <strong>pending</strong> via le service de rendez-vous existant. Le médecin devra l’accepter.
                            </p>
                            <ul class="text-sm text-[#131b2e] space-y-1.5">
                                <li><strong>Médecin :</strong> Dr. {{ $doctorUser->name }}</li>
                                <li><strong>Spécialité :</strong> {{ $doctor->speciality->name }}</li>
                                <li><strong>Date :</strong> {{ $availability->date->translatedFormat('l j F Y') }}</li>
                                <li><strong>Heure :</strong> {{ $timeLabel }}</li>
                                <li><strong>Patient :</strong> {{ $patient->name }}</li>
                            </ul>
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                <button type="button" class="w-full sm:w-auto px-6 py-3 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-sm font-semibold inline-flex items-center justify-center gap-1" @click="prev()">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                                    Retour à l’étape 3
                                </button>
                                <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white text-sm font-bold inline-flex items-center justify-center gap-1 shadow-md" :disabled="submitting">
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock</span>
                                    <span x-show="!submitting">Confirmer et réserver le créneau</span>
                                    <span x-show="submitting" x-cloak>Envoi en cours…</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                </button>
                            </div>
                        </div>
                        <noscript>
                            <div class="bg-white rounded-2xl p-5 shadow-sm">
                                <button type="submit" class="w-full px-8 py-3 rounded-lg bg-[#004ac6] text-white text-sm font-bold">
                                    Envoyer la demande de rendez-vous
                                </button>
                            </div>
                        </noscript>
                    </div>

                    <aside class="lg:col-span-4 flex flex-col gap-6 lg:sticky lg:top-24 min-w-0">
                        <section class="bg-white rounded-2xl p-6 shadow-sm">
                            <div class="flex items-center justify-between pb-2">
                                <span class="text-[11px] uppercase tracking-wider text-[#434655] font-bold">Profil du médecin</span>
                                <span class="px-2 py-0.5 rounded-full bg-[#dae2fd] text-[#434655] text-[11px] font-semibold">Fiche réelle</span>
                            </div>
                            <div class="flex items-start gap-4 pt-2">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-[#004ac6] text-white flex items-center justify-center font-bold shrink-0 shadow-sm">
                                    @if($photo)
                                        <img src="{{ $photo }}" alt="Photo de Dr. {{ $doctorUser->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($doctorUser->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-display text-[20px] leading-7 font-bold truncate">Dr. {{ $doctorUser->name }}</h3>
                                    <p class="text-[11px] text-[#434655]">{{ $doctor->speciality->name }}</p>
                                    <p class="text-[11px] text-[#434655] mt-1">Aucun avis patient n’est encore enregistré.</p>
                                </div>
                            </div>
                            @if($doctor->address)
                                <div class="mt-4 pt-4 flex items-center gap-2 text-[13px] text-[#434655]">
                                    <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">corporate_fare</span>
                                    <span class="truncate">{{ $doctor->address }}</span>
                                </div>
                            @endif
                        </section>

                        <section class="bg-white rounded-2xl p-6 shadow-sm">
                            <h3 class="font-display text-[16px] leading-6 font-bold mb-4">Détails du créneau</h3>
                            <div class="space-y-4">
                                <div class="flex items-start gap-4 p-4 rounded-xl bg-[#f2f3ff]">
                                    <div class="p-1.5 bg-[#2563eb] text-white rounded-lg shrink-0">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_today</span>
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ $availability->date->translatedFormat('l j F Y') }}</p>
                                        <p class="text-[13px] text-[#004ac6] font-semibold">{{ $timeLabel }}</p>
                                        @if($duration)
                                            <p class="text-[11px] text-[#434655] mt-0.5">Durée : {{ $duration }} min</p>
                                        @endif
                                    </div>
                                </div>
                                @if($doctor->address)
                                    <div class="flex items-start gap-4 p-4 rounded-xl bg-[#f2f3ff]">
                                        <div class="p-1.5 bg-[#006a61] text-white rounded-lg shrink-0">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">location_on</span>
                                        </div>
                                        <div>
                                            <p class="font-bold">Présentiel</p>
                                            <p class="text-[13px] text-[#434655]">{{ $doctor->address }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="bg-white rounded-2xl p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-display text-[16px] leading-6 font-bold">Récapitulatif financier</h3>
                                <span class="text-[11px] font-bold text-[#006242] bg-[#007d55]/15 px-2 py-0.5 rounded">Hors plateforme</span>
                            </div>
                            <div class="space-y-2 text-[13px]">
                                <div class="flex justify-between text-[#131b2e]">
                                    <span>Consultation {{ $doctor->speciality->name }}</span>
                                    <span class="font-semibold">Non tarifée ici</span>
                                </div>
                                <div class="flex justify-between text-[#434655]">
                                    <span>Frais SantéConnect</span>
                                    <span class="font-semibold text-[#006242]">Aucun</span>
                                </div>
                                <div class="mt-2 bg-[#e2e7ff]/60 p-4 rounded-xl">
                                    <p class="font-bold">Total dû aujourd’hui</p>
                                    <p class="text-[11px] text-[#434655]">Aucun encaissement n’est géré par l’application.</p>
                                    <p class="font-display text-[32px] leading-10 font-bold text-[#004ac6] mt-1">—</p>
                                </div>
                            </div>
                        </section>

                        <section class="bg-white rounded-2xl p-6 shadow-sm space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#006242] text-[20px]" aria-hidden="true">sentiment_satisfied</span>
                                <p class="text-[11px] font-semibold">Garanties patient</p>
                            </div>
                            <ul class="space-y-1 text-[13px] text-[#434655]">
                                <li class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-[#006242]" aria-hidden="true">alarm_on</span>
                                    <span>Annulation possible tant que le rendez-vous est futur et encore en attente ou accepté.</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-[#006242]" aria-hidden="true">mark_email_read</span>
                                    <span>Confirmation affichée après envoi réel de la demande, pas avant.</span>
                                </li>
                            </ul>
                            <div class="p-3 rounded-xl bg-[#ffdad6]/60 text-[#93000a] space-y-1">
                                <div class="flex items-center gap-1 text-[12px] font-bold text-[#ba1a1a]">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">emergency</span>
                                    <span>Urgence médicale</span>
                                </div>
                                <p class="text-[11px] leading-normal">
                                    En cas de douleur thoracique aiguë ou de détresse, appelez le <strong>15</strong> ou le <strong>112</strong> immédiatement.
                                </p>
                            </div>
                            @if($doctor->phone)
                                <div class="flex items-center justify-between text-[11px] text-[#434655]">
                                    <span>Cabinet :</span>
                                    <a class="text-[#004ac6] font-bold hover:underline" href="tel:{{ $doctor->phone }}">{{ $doctor->phone }}</a>
                                </div>
                            @endif
                        </section>
                    </aside>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
