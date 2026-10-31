@php
    $doctorUser = $doctor->user;
    $photo = $doctorUser->profile_image_url ?? ($doctor->photo ? asset('storage/'.$doctor->photo) : null);
    $minDate = now()->addDay()->format('Y-m-d');
    $durationOptions = [
        15 => ['label' => '15 min', 'hint' => 'Court'],
        20 => ['label' => '20 min', 'hint' => 'Suivi rapide'],
        30 => ['label' => '30 min', 'hint' => 'Standard'],
        45 => ['label' => '45 min', 'hint' => 'Bilan'],
        60 => ['label' => '60 min', 'hint' => 'Long'],
    ];
@endphp

@extends('layouts.doctor')
@section('page-title', 'Ajouter une disponibilité')
@section('page-subtitle', 'Définissez le jour, les horaires et le mode de création')

@section('doctor-content')
<div class="sc-stack w-full"
     x-data="{
        mode: @js(old('mode', 'generate') === 'single' ? 'single' : 'generate'),
        date: @js((string) old('date', '')),
        startTime: @js((string) old('start_time', '09:00')),
        endTime: @js((string) old('end_time', '09:30')),
        periodStart: @js((string) old('period_start', '09:00')),
        periodEnd: @js((string) old('period_end', '13:00')),
        duration: @js((int) old('duration_minutes', 30)),
        existing: @js($existingSlots),
        toMin(value) {
            const parts = String(value || '00:00').split(':');
            return (Number(parts[0]) || 0) * 60 + (Number(parts[1]) || 0);
        },
        fromMin(total) {
            const hours = Math.floor(total / 60);
            const minutes = total % 60;
            return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        },
        overlaps(date, start, end) {
            return this.existing.some((slot) => (
                slot.date === date
                && this.toMin(slot.start) < this.toMin(end)
                && this.toMin(slot.end) > this.toMin(start)
            ));
        },
        get singleValid() {
            return this.date && this.startTime && this.endTime && this.toMin(this.startTime) < this.toMin(this.endTime);
        },
        get singleMinutes() {
            return this.singleValid ? this.toMin(this.endTime) - this.toMin(this.startTime) : 0;
        },
        get singleOverlap() {
            return this.singleValid && this.overlaps(this.date, this.startTime, this.endTime);
        },
        get generatedSlots() {
            const start = this.toMin(this.periodStart);
            const end = this.toMin(this.periodEnd);
            const duration = Number(this.duration) || 0;
            const slots = [];
            if (!this.date || start >= end || duration < 1) {
                return slots;
            }
            let current = start;
            while (current + duration <= end) {
                const slotStart = this.fromMin(current);
                const slotEnd = this.fromMin(current + duration);
                slots.push({
                    start: slotStart,
                    end: slotEnd,
                    skip: this.overlaps(this.date, slotStart, slotEnd),
                });
                current += duration;
            }
            return slots;
        },
        get generatedMinutes() {
            return this.date && this.toMin(this.periodStart) < this.toMin(this.periodEnd)
                ? this.toMin(this.periodEnd) - this.toMin(this.periodStart)
                : 0;
        },
        get createdCount() {
            return this.mode === 'generate'
                ? this.generatedSlots.filter((slot) => !slot.skip).length
                : (this.singleValid && !this.singleOverlap ? 1 : 0);
        },
        get skippedCount() {
            return this.mode === 'generate'
                ? this.generatedSlots.filter((slot) => slot.skip).length
                : (this.singleOverlap ? 1 : 0);
        },
        amplitudeMinutes() {
            return this.mode === 'generate' ? this.generatedMinutes : this.singleMinutes;
        },
        hoursLabel(minutes) {
            if (minutes < 1) {
                return '0';
            }
            if (minutes < 60) {
                return String(minutes);
            }
            const hours = Math.floor(minutes / 60);
            const rest = minutes % 60;
            if (rest === 0) {
                return String(hours);
            }
            return hours + 'h' + String(rest).padStart(2, '0');
        },
        amplitudeUnit(minutes) {
            if (minutes >= 60 && minutes % 60 === 0) {
                return 'heures';
            }
            if (minutes >= 60) {
                return '';
            }
            return 'min';
        }
     }">

    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <a class="inline-flex items-center gap-1 text-[#004ac6] text-[14px] font-semibold hover:text-[#003ea8] transition-colors group"
               href="{{ route('doctor.availabilities.index') }}">
                <span class="material-symbols-outlined text-[18px] transition-transform group-hover:-translate-x-1" aria-hidden="true">arrow_back</span>
                <span>Retour à mes disponibilités</span>
            </a>
            <nav aria-label="Fil d'Ariane" class="flex items-center gap-1.5 text-[#737686] text-[11px]">
                <span>Espace praticien</span>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <a class="hover:text-[#004ac6]" href="{{ route('doctor.availabilities.index') }}">Planning</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span class="text-[#131b2e] font-semibold">Ajouter une disponibilité</span>
            </nav>
        </div>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pt-1">
            <div class="flex flex-col gap-1">
                <div class="inline-flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full bg-[#dbe1ff] text-[#00174b] text-[11px] font-semibold uppercase tracking-wider">Configuration Agenda</span>
                    @if($doctor->address)
                        <span class="px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[#434655] text-[11px]">{{ $doctor->address }}</span>
                    @endif
                </div>
                <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 tracking-tight">Ajouter une disponibilité</h2>
                <p class="text-[15px] leading-6 text-[#434655] max-w-2xl">
                    Créez un créneau unique ou générez une plage de consultations pour une date future.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-xl p-4 bg-[#f2f3ff] shadow-sm flex items-start gap-2">
        <span class="material-symbols-outlined text-[#004ac6] text-[22px] mt-0.5" aria-hidden="true">info</span>
        <div class="flex-1">
            <p class="text-[14px] font-semibold text-[#131b2e]">Créneau daté, sans récurrence</p>
            <p class="text-[13px] leading-5 text-[#434655]">
                La date doit être après aujourd’hui. La génération découpe la plage et ignore les horaires déjà occupés. Un créneau unique qui chevauche un existant est refusé.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-3 rounded-lg bg-[#ffdad6] text-[#93000a] text-[13px] flex items-start gap-2" role="alert">
            <span class="material-symbols-outlined text-[18px] shrink-0" aria-hidden="true">error</span>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex bg-[#f2f3ff] p-1 rounded-xl gap-1" role="tablist" aria-label="Mode de création">
        <button type="button"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all"
                :class="mode === 'single' ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655]'"
                @click="mode = 'single'"
                :aria-selected="(mode === 'single').toString()">
            <span class="material-symbols-outlined text-base" aria-hidden="true">add_circle</span>
            Créneau unique
        </button>
        <button type="button"
                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all"
                :class="mode === 'generate' ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655]'"
                @click="mode = 'generate'"
                :aria-selected="(mode === 'generate').toString()">
            <span class="material-symbols-outlined text-base" aria-hidden="true">auto_fix_high</span>
            Génération automatique
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        <div class="xl:col-span-7 flex flex-col gap-6">
            <form method="POST" action="{{ route('doctor.availabilities.store') }}"
                  class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-6"
                  x-show="mode === 'single'">
                @csrf
                <input type="hidden" name="mode" value="single">

                <div class="flex items-center justify-between pb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-[#e2e7ff] flex items-center justify-center text-[#004ac6]">
                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">tune</span>
                        </div>
                        <div>
                            <h3 class="font-display text-[16px] leading-6">Configuration du créneau</h3>
                            <p class="text-[13px] text-[#737686]">Un seul horaire pour la date choisie</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-full bg-[#86f2e4] text-[#006f66] text-[11px] font-semibold">Créneau unique</span>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[14px] font-semibold text-[#131b2e]" for="s-date">
                        Date <span class="text-[#ba1a1a]">*</span>
                    </label>
                    <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb] transition-all"
                           id="s-date" name="date" type="date" required min="{{ $minDate }}" x-model="date">
                    @error('date')
                        <p class="text-[#ba1a1a] text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] font-semibold text-[#131b2e]" for="s-start">
                            Heure de début <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">schedule</span>
                            <input class="w-full h-11 pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb] transition-all"
                                   id="s-start" name="start_time" type="time" required x-model="startTime">
                        </div>
                        @error('start_time')
                            <p class="text-[#ba1a1a] text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] font-semibold text-[#131b2e]" for="s-end">
                            Heure de fin <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">more_time</span>
                            <input class="w-full h-11 pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb] transition-all"
                                   id="s-end" name="end_time" type="time" required x-model="endTime">
                        </div>
                        @error('end_time')
                            <p class="text-[#ba1a1a] text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="text-[13px] text-[#434655] flex items-center gap-1.5" x-show="singleValid">
                    <span class="material-symbols-outlined text-[16px] text-[#006242]" aria-hidden="true">check_circle</span>
                    <span>Durée du créneau : <span x-text="singleMinutes + ' min'"></span></span>
                </p>

                <div class="flex items-center justify-between flex-wrap gap-3 pt-2">
                    <a class="px-4 py-2.5 rounded-lg bg-[#e2e7ff] text-[#434655] text-[14px] font-semibold hover:bg-[#dae2fd] flex items-center gap-2"
                       href="{{ route('doctor.availabilities.index') }}">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                        Annuler
                    </a>
                    <button class="px-6 py-2.5 rounded-lg bg-[#2563eb] text-white text-[14px] font-semibold shadow-sm hover:bg-[#004ac6] flex items-center gap-2"
                            type="submit">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add_task</span>
                        Ajouter la disponibilité
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('doctor.availabilities.store') }}"
                  class="flex flex-col gap-6"
                  x-show="mode === 'generate'"
                  x-cloak>
                @csrf
                <input type="hidden" name="mode" value="generate">

                <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-6">
                <div class="flex items-center justify-between pb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-[#e2e7ff] flex items-center justify-center text-[#004ac6]">
                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">tune</span>
                        </div>
                        <div>
                            <h3 class="font-display text-[16px] leading-6">Configuration du créneau horaire</h3>
                            <p class="text-[13px] text-[#737686]">Définissez la date, les horaires et la durée de chaque consultation</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-full bg-[#86f2e4] text-[#006f66] text-[11px] font-semibold">Date unique</span>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[14px] font-semibold text-[#131b2e]" for="g-date">
                        Date <span class="text-[#ba1a1a]">*</span>
                    </label>
                    <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb] transition-all"
                           id="g-date" name="date" type="date" required min="{{ $minDate }}" x-model="date">
                    @error('date')
                        <p class="text-[#ba1a1a] text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] font-semibold text-[#131b2e]" for="g-start">
                            Heure de début <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">schedule</span>
                            <input class="w-full h-11 pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb] transition-all"
                                   id="g-start" name="period_start" type="time" required x-model="periodStart">
                        </div>
                        @error('period_start')
                            <p class="text-[#ba1a1a] text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] font-semibold text-[#131b2e]" for="g-end">
                            Heure de fin <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">more_time</span>
                            <input class="w-full h-11 pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb] transition-all"
                                   id="g-end" name="period_end" type="time" required x-model="periodEnd">
                        </div>
                        @error('period_end')
                            <p class="text-[#ba1a1a] text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-[13px] text-[#434655] flex items-center gap-1.5" x-show="generatedMinutes > 0">
                    <span class="material-symbols-outlined text-[16px] text-[#006242]" aria-hidden="true">check_circle</span>
                    <span>La plage doit contenir au moins un créneau de 15 minutes (actuellement : <span x-text="hoursLabel(generatedMinutes) + ' ' + amplitudeUnit(generatedMinutes)"></span>).</span>
                </p>

                <div class="flex flex-col gap-2">
                    <span class="text-[14px] font-semibold text-[#131b2e]">Durée par consultation <span class="text-[#ba1a1a]">*</span></span>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        @foreach($durationOptions as $val => $meta)
                            <label class="relative flex flex-col items-center justify-center p-3 rounded-lg cursor-pointer transition-all shadow-sm"
                                   :class="Number(duration) === {{ $val }} ? 'bg-[#2563eb] text-white' : 'bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e]'">
                                <input class="sr-only" name="duration_minutes" type="radio" value="{{ $val }}"
                                       x-model.number="duration" {{ (int) old('duration_minutes', 30) === $val ? 'checked' : '' }}>
                                <span class="text-[14px] font-bold">{{ $meta['label'] }}</span>
                                <span class="text-[11px] opacity-80">{{ $meta['hint'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('duration_minutes')
                        <p class="text-[#ba1a1a] text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <span class="text-[14px] font-semibold text-[#131b2e]">Lieu de consultation</span>
                    <div class="flex flex-col gap-2 p-4 rounded-xl bg-[#dae2fd] ring-2 ring-[#004ac6]">
                        <div class="flex items-center justify-between">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[#004ac6]">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">apartment</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[14px] font-bold text-[#131b2e]">Au cabinet</p>
                            <p class="text-[11px] text-[#434655]">Présentiel uniquement — la vidéo n’est pas gérée.</p>
                        </div>
                    </div>
                </div>
                </div>

                <div class="flex items-center justify-between flex-wrap gap-4 p-4 rounded-xl bg-white shadow-sm">
                    <a class="px-4 py-2.5 rounded-lg bg-[#eaedff] text-[#434655] text-[14px] font-semibold hover:bg-[#dae2fd] flex items-center gap-2"
                       href="{{ route('doctor.availabilities.index') }}">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                        Annuler
                    </a>
                    <button class="px-6 py-2.5 rounded-lg bg-[#2563eb] text-white text-[14px] font-semibold shadow-sm hover:brightness-110 flex items-center gap-2"
                            type="submit">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add_task</span>
                        Ajouter la disponibilité
                    </button>
                </div>
            </form>
        </div>

        <div class="xl:col-span-5 flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-6 xl:sticky xl:top-24">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-[#86f2e4] flex items-center justify-center text-[#006f66]">
                            <span class="material-symbols-outlined text-[24px]" aria-hidden="true">visibility</span>
                        </div>
                        <div>
                            <h3 class="font-display text-[16px] leading-6">Aperçu de la plage générée</h3>
                            <span class="text-[11px] text-[#737686]">Calcul automatique à partir du formulaire</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-[#eaedff] text-[#004ac6] text-[11px] font-semibold">Aperçu</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="p-4 rounded-xl bg-[#f2f3ff] flex flex-col">
                        <span class="text-[11px] text-[#737686]">Amplitude totale</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="font-display text-[28px] leading-9 font-bold" x-text="hoursLabel(amplitudeMinutes())">0</span>
                            <span class="text-[12px] text-[#434655]" x-text="amplitudeUnit(amplitudeMinutes())"></span>
                        </div>
                        <span class="text-[11px] text-[#004ac6] mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">schedule</span>
                            <span x-text="mode === 'generate' ? (periodStart + ' → ' + periodEnd) : (startTime + ' → ' + endTime)"></span>
                        </span>
                    </div>
                    <div class="p-4 rounded-xl bg-[#f2f3ff] flex flex-col">
                        <span class="text-[11px] text-[#737686]">Capacité d'accueil</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="font-display text-[28px] leading-9 font-bold text-[#007d55]" x-text="createdCount">0</span>
                            <span class="text-[12px] text-[#434655]">créneau(x)</span>
                        </div>
                        <span class="text-[11px] text-[#006242] mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">timer</span>
                            <span x-text="mode === 'generate' ? (duration + ' min / créneau') : (singleMinutes + ' min')"></span>
                        </span>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-[#f2f3ff] flex items-start gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white shrink-0 mt-0.5"
                         :class="skippedCount > 0 ? 'bg-[#ba1a1a]' : 'bg-[#006242]'">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true" x-text="skippedCount > 0 ? 'warning' : 'check'"></span>
                    </div>
                    <div>
                        <p class="text-[12px] font-bold text-[#131b2e]"
                           x-text="skippedCount > 0
                                ? (mode === 'generate' ? skippedCount + ' horaire(s) déjà occupé(s) seront ignorés' : 'Ce créneau chevauche une disponibilité existante')
                                : 'Aucun chevauchement avec vos créneaux déjà enregistrés'"></p>
                        <p class="text-[13px] text-[#737686]">Les créneaux adjacents (fin = début du suivant) restent possibles.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-1" x-show="mode === 'generate' && generatedSlots.length">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-[#131b2e] uppercase tracking-wider">Créneaux calculés</span>
                        <span class="text-[11px] text-[#737686]" x-text="generatedSlots.length + ' au total'"></span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 pt-1">
                        <template x-for="slot in generatedSlots" :key="slot.start + '-' + slot.end">
                            <div class="flex flex-col items-center justify-center p-2 rounded-lg text-center"
                                 :class="slot.skip ? 'bg-[#ffdad6] text-[#93000a]' : 'bg-[#eaedff] text-[#131b2e]'">
                                <span class="text-[12px] font-bold" x-text="slot.start"></span>
                                <span class="text-[10px] text-[#737686]" x-text="slot.skip ? 'occupé' : (duration + ' min')"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-[#f2f3ff] flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-[#e2e7ff] flex items-center justify-center shrink-0">
                            @if($photo)
                                <img alt="Photo de Dr. {{ $doctorUser->name }}" class="w-full h-full object-cover" src="{{ $photo }}">
                            @else
                                <span class="font-display font-bold text-[#004ac6]" aria-hidden="true">{{ strtoupper(substr($doctorUser->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-[12px] font-bold text-[#131b2e] truncate">Dr. {{ $doctorUser->name }}</p>
                            <p class="text-[11px] text-[#434655] truncate">{{ $doctor->address ?: 'Adresse non renseignée' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-2 p-3 rounded-lg bg-[#eaedff] text-[#434655]">
                    <span class="material-symbols-outlined text-[20px] text-[#004ac6] shrink-0" aria-hidden="true">lightbulb</span>
                    <p class="text-[13px] leading-5">
                        <strong class="font-semibold text-[#131b2e]">Règle métier :</strong>
                        un créneau unique qui chevauche un existant est refusé. En génération, les horaires déjà pris sont simplement ignorés.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
