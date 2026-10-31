@extends('layouts.app')
@section('title', 'Trouver un médecin')

@php
    $activeSpecialty = $specialties->firstWhere('id', (int) request('speciality', request('specialty_id')));
    $catalog = $doctors->map(function ($doctor) {
        $slots = $doctor->availabilities ?? collect();
        $days = $slots->groupBy(fn ($slot) => $slot->date->format('Y-m-d'))->take(4);
        $photo = $doctor->user->profile_image_url
            ?? ($doctor->photo ? asset('storage/'.$doctor->photo) : null);

        return [
            'id' => $doctor->id,
            'name' => $doctor->user->name,
            'specialty' => $doctor->speciality->name,
            'address' => $doctor->address ?: '',
            'photo' => $photo,
            'profile' => route('patient.doctors.show', $doctor),
            'count' => (int) $doctor->available_slots_count,
            'maps' => $doctor->address
                ? 'https://www.google.com/maps/search/?'.http_build_query(['api' => 1, 'query' => $doctor->address])
                : null,
            'preview' => $slots->take(4)->map(fn ($slot) => [
                'id' => $slot->id,
                'date' => $slot->date->format('Y-m-d'),
                'time' => substr($slot->start_time, 0, 5),
                'href' => route('patient.appointments.create', ['availability_id' => $slot->id]),
            ])->values(),
            'days' => $days->map(function ($daySlots, $date) {
                $carbon = \Carbon\Carbon::parse($date);

                return [
                    'date' => $date,
                    'label' => $carbon->translatedFormat('D'),
                    'full' => $carbon->translatedFormat('D j M'),
                    'num' => $carbon->format('j'),
                    'isToday' => $carbon->isToday(),
                    'count' => $daySlots->count(),
                    'slots' => $daySlots->map(fn ($slot) => [
                        'id' => $slot->id,
                        'time' => substr($slot->start_time, 0, 5),
                        'hour' => (int) substr($slot->start_time, 0, 2),
                        'href' => route('patient.appointments.create', ['availability_id' => $slot->id]),
                    ])->values(),
                ];
            })->values(),
        ];
    })->values();
    $initial = $catalog->first(fn ($doctor) => $doctor['days']->isNotEmpty()) ?? $catalog->first();
    $directoryPayload = [
        'doctors' => $catalog,
        'selectedId' => $initial['id'] ?? null,
        'selectedDay' => $initial['days'][0]['date'] ?? null,
        'selectedSlot' => $initial['days'][0]['slots'][0]['id'] ?? null,
    ];
@endphp

@section('content')
<div class="bg-[#faf8ff] min-h-screen text-[#131b2e] antialiased"
     x-data="scDirectory({{ \Illuminate\Support\Js::from($directoryPayload) }})">
    <div class="relative sc-page-wide pt-6 sm:pt-8 pb-8 sm:pb-10">

        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <nav class="flex items-center gap-1 text-[12px] font-semibold tracking-[0.02em] text-[#434655] flex-wrap" aria-label="Fil d’Ariane">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-[#004ac6] transition-colors">SantéConnect</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span class="text-[#131b2e] font-semibold">Annuaire des spécialistes</span>
                @if($activeSpecialty)
                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                    <span class="px-1 py-0.5 rounded-full bg-[#eaedff] text-[#004ac6] font-medium">{{ $activeSpecialty->name }}</span>
                @endif
            </nav>
            <p class="flex items-center gap-1.5 text-[11px] text-[#006242]">
                <span class="w-2 h-2 rounded-full bg-[#006242] animate-pulse"></span>
                Créneaux issus de l’agenda réel
            </p>
        </div>

        <section class="w-full bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-6">
            <form method="GET" action="{{ route('patient.doctors.index') }}" role="search">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
                    <div class="lg:col-span-4">
                        <label for="doctor-search" class="flex items-center gap-1 px-4 py-2.5 rounded-lg bg-[#f2f3ff] focus-within:bg-white focus-within:shadow-md transition-all">
                            <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">stethoscope</span>
                            <span class="flex flex-col flex-1 min-w-0">
                                <span class="text-[11px] text-[#434655] uppercase tracking-wider">Spécialité ou médecin</span>
                                <input id="doctor-search" name="search" type="search" value="{{ request('search') }}"
                                       placeholder="Nom du médecin ou spécialité"
                                       class="bg-transparent border-0 p-0 font-display text-[16px] leading-6 text-[#131b2e] focus:ring-0 truncate w-full">
                            </span>
                        </label>
                    </div>
                    <div class="lg:col-span-3">
                        <div class="flex items-center gap-1 px-4 py-2.5 rounded-lg bg-[#f2f3ff] focus-within:bg-white focus-within:shadow-md transition-all">
                            <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">category</span>
                            <span class="flex flex-col flex-1 min-w-0">
                                <span class="text-[11px] text-[#434655] uppercase tracking-wider">Spécialité</span>
                                <x-ui.select name="speciality" id="doctor-speciality" label="Spécialité" :plain="true">
                                    <option value="">Toutes les spécialités</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}" {{ (string) request('speciality', request('specialty_id')) === (string) $specialty->id ? 'selected' : '' }}>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </span>
                        </div>
                    </div>
                    <div class="lg:col-span-3">
                        <div class="flex items-center gap-1 px-4 py-2.5 rounded-lg bg-[#f2f3ff] focus-within:bg-white focus-within:shadow-md transition-all">
                            <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">calendar_today</span>
                            <span class="flex flex-col flex-1 min-w-0">
                                <span class="text-[11px] text-[#434655] uppercase tracking-wider">Date et disponibilités</span>
                                <x-ui.select name="when" id="doctor-when" label="Date et disponibilités" :plain="true">
                                    <option value="" {{ request('when') === null || request('when') === '' ? 'selected' : '' }}>Toutes les dates</option>
                                    <option value="today" {{ request('when') === 'today' ? 'selected' : '' }}>Aujourd’hui</option>
                                    <option value="today_tomorrow" {{ request('when') === 'today_tomorrow' ? 'selected' : '' }}>Aujourd’hui et demain</option>
                                    <option value="48h" {{ request('when') === '48h' ? 'selected' : '' }}>Sous 48 heures</option>
                                    <option value="7d" {{ request('when') === '7d' ? 'selected' : '' }}>7 prochains jours</option>
                                </x-ui.select>
                            </span>
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <button type="submit" class="w-full h-[46px] rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white text-[14px] font-semibold flex items-center justify-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-[19px]" aria-hidden="true">search</span>
                            Rechercher
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-4 mt-2">
                    <div class="flex flex-wrap items-center gap-1">
                        <span class="text-[11px] text-[#434655] mr-1">Filtres rapides :</span>
                        @if($activeSpecialty)
                            <a href="{{ route('patient.doctors.index', request()->except('speciality', 'specialty_id', 'page')) }}"
                               class="px-2 py-1 rounded-full bg-[#dbe1ff] text-[#00174b] text-[11px] font-semibold inline-flex items-center gap-1 shadow-sm">
                                Spécialité : {{ $activeSpecialty->name }}
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">close</span>
                            </a>
                        @endif
                        <button type="button" class="px-2 py-1 rounded-full bg-[#eaedff] text-[#131b2e] text-[11px] font-medium inline-flex items-center gap-1"
                                @click="toast('Le type de consultation vidéo n’est pas géré.')">
                            Consultation en cabinet
                            <span class="material-symbols-outlined text-[14px] text-[#737686]" aria-hidden="true">tune</span>
                        </button>
                        <button type="button" class="px-2 py-1 rounded-full bg-[#eaedff] text-[#131b2e] text-[11px] font-medium inline-flex items-center gap-1"
                                @click="toast('Aucun filtre de langue n’est enregistré.')">
                            Langue
                            <span class="material-symbols-outlined text-[14px] text-[#737686]" aria-hidden="true">tune</span>
                        </button>
                        <button type="button" class="px-2 py-1 rounded-full bg-[#eaedff] text-[#131b2e] text-[11px] font-medium inline-flex items-center gap-1"
                                @click="toast('Aucun tarif n’est enregistré.')">
                            Tarif
                            <span class="material-symbols-outlined text-[14px] text-[#737686]" aria-hidden="true">tune</span>
                        </button>
                    </div>
                    @if($hasFilters)
                        <a href="{{ route('patient.doctors.index') }}" class="text-[11px] text-[#004ac6] hover:underline font-semibold inline-flex items-center gap-0.5">
                            Réinitialiser
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">refresh</span>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold text-[#131b2e]">
                    {{ $doctors->total() }} médecin{{ $doctors->total() > 1 ? 's' : '' }} disponible{{ $doctors->total() > 1 ? 's' : '' }}
                </h1>
                @if($doctors->total() > 0)
                    <span class="px-2.5 py-0.5 rounded-full bg-[#eaedff] text-[#434655] text-[11px] font-semibold">Profils validés</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <label for="doctor-sort" class="text-[11px] text-[#434655]">Trier par :</label>
                <form method="GET" action="{{ route('patient.doctors.index') }}" class="relative">
                    @foreach(request()->except('sort', 'direction', 'page') as $key => $value)
                        @if(is_scalar($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <div class="min-w-[9rem]">
                        <x-ui.select name="sort" id="doctor-sort" label="Trier par" onchange="this.form.submit()">
                            <option value="name" {{ request('sort', 'name') === 'name' ? 'selected' : '' }}>Nom</option>
                            <option value="speciality" {{ request('sort') === 'speciality' ? 'selected' : '' }}>Spécialité</option>
                        </x-ui.select>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-7 space-y-4">
                @forelse($doctors as $doctor)
                    @php
                        $entry = $catalog->firstWhere('id', $doctor->id);
                        $slotCount = (int) $doctor->available_slots_count;
                        $photo = $entry['photo'] ?? null;
                        $todaySlots = ($doctor->availabilities ?? collect())->filter(fn ($slot) => $slot->date->isToday());
                    @endphp
                    <article class="relative w-full bg-white rounded-xl p-4 lg:p-6 transition-all"
                             :class="selectedId === {{ $doctor->id }} ? 'shadow-md' : 'shadow-sm hover:shadow-md'">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#004ac6] rounded-l-xl" x-show="selectedId === {{ $doctor->id }}" x-cloak></div>
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            <div class="relative shrink-0">
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden shadow-sm bg-[#e2e7ff] flex items-center justify-center">
                                    @if($photo)
                                        <img src="{{ $photo }}" alt="Photo de Dr. {{ $doctor->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[#004ac6] text-2xl font-bold font-display" aria-hidden="true">{{ strtoupper(substr($doctor->user->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                @if($todaySlots->isNotEmpty())
                                    <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded-full bg-[#007d55] text-white text-[10px] font-bold shadow-sm">Aujourd’hui</span>
                                @elseif($slotCount > 0)
                                    <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded-full bg-[#006a61] text-white text-[10px] font-bold shadow-sm">Ouvert</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-start justify-between gap-1 mb-1">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <h2 class="font-display text-[20px] leading-7 font-bold text-[#131b2e]">Dr. {{ $doctor->user->name }}</h2>
                                            <span class="material-symbols-outlined text-[#004ac6] text-[18px]" title="Profil validé" aria-hidden="true">verified</span>
                                        </div>
                                        <p class="text-[13px] text-[#004ac6] font-medium">{{ $doctor->speciality->name }}</p>
                                    </div>
                                </div>
                                <p class="text-[13px] text-[#434655] flex items-center gap-1 mb-1">
                                    <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">local_hospital</span>
                                    <span class="truncate">{{ $doctor->address ?: 'Adresse du cabinet non renseignée.' }}</span>
                                </p>
                                <div class="flex flex-wrap items-center gap-4 text-[12px] text-[#434655] my-1">
                                    <span class="inline-flex items-center gap-1 font-semibold text-[#131b2e]">
                                        <span class="material-symbols-outlined text-[16px] text-[#004ac6]" aria-hidden="true">event_available</span>
                                        {{ $slotCount > 0 ? $slotCount.' créneau'.($slotCount > 1 ? 'x disponibles' : ' disponible') : 'Aucun créneau disponible' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 my-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-full bg-[#eaedff] text-[#131b2e] text-[11px] font-semibold inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[13px] text-[#006a61]" aria-hidden="true">apartment</span>
                                        Consultation en cabinet
                                    </span>
                                </div>
                                @if($entry && $entry['preview']->isNotEmpty())
                                    <div class="pt-1">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-[11px] text-[#131b2e] font-semibold inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[15px] text-[#006242]" aria-hidden="true">schedule</span>
                                                Prochains créneaux
                                            </span>
                                            <a href="{{ route('patient.doctors.show', $doctor) }}" class="text-[11px] text-[#004ac6] hover:underline">Profil complet →</a>
                                        </div>
                                        <div class="grid grid-cols-4 gap-1">
                                            @foreach($entry['preview'] as $preview)
                                                <a href="{{ $preview['href'] }}"
                                                   class="py-1.5 px-2 rounded-lg text-[12px] font-semibold text-center transition-colors"
                                                   :class="selectedSlot === {{ $preview['id'] }} ? 'bg-[#004ac6] text-white shadow-sm' : 'bg-[#eaedff] text-[#131b2e] hover:bg-[#e2e7ff]'"
                                                   @click.prevent="pickSlot({{ $doctor->id }}, {{ $preview['id'] }}, {{ \Illuminate\Support\Js::from($preview['date']) }})">
                                                    {{ $preview['time'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-2 p-2 rounded-lg bg-[#f2f3ff] flex items-center justify-between gap-2">
                                        <span class="text-[12px] text-[#434655]">Aucun créneau libre pour le moment.</span>
                                        <a href="{{ route('patient.doctors.show', $doctor) }}" class="text-[12px] text-[#004ac6] font-bold hover:underline">Voir le profil</a>
                                    </div>
                                @endif
                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <button type="button" class="text-[12px] text-[#004ac6] font-bold hover:underline"
                                            @click="selectDoctor({{ $doctor->id }})">
                                        Sélectionner →
                                    </button>
                                    <a href="{{ route('patient.doctors.show', $doctor) }}"
                                       class="px-4 py-1.5 rounded-lg bg-[#e2e7ff] hover:bg-[#004ac6] hover:text-white text-[#131b2e] text-[12px] font-semibold transition-all">
                                        {{ $slotCount > 0 ? 'Prendre rendez-vous' : 'Voir le profil' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-xl py-16 text-center shadow-sm">
                        <span class="material-symbols-outlined text-6xl text-[#c3c6d7] block mb-4" aria-hidden="true">stethoscope</span>
                        @if($hasFilters)
                            <p class="font-semibold text-[#131b2e] mb-1">Aucun médecin ne correspond à votre recherche</p>
                            <p class="text-sm text-[#434655] mb-4">Modifiez le nom, la spécialité ou la date, puis relancez la recherche.</p>
                            <a href="{{ route('patient.doctors.index') }}"
                               class="inline-flex items-center px-5 py-2.5 bg-[#004ac6] text-white rounded-lg text-sm font-semibold">
                                Voir tous les médecins
                            </a>
                        @else
                            <p class="font-semibold text-[#131b2e] mb-1">Aucun médecin disponible</p>
                            <p class="text-sm text-[#434655]">Revenez plus tard : de nouveaux profils seront ajoutés après validation.</p>
                        @endif
                    </div>
                @endforelse

                @if($doctors->isNotEmpty())
                    <div class="bg-white p-4 rounded-2xl shadow-sm sc-pager">
                        <p class="sc-pager__label">
                            Affichage de <span class="font-semibold text-[#131b2e]">{{ $doctors->firstItem() }}</span>
                            à <span class="font-semibold text-[#131b2e]">{{ $doctors->lastItem() }}</span>
                            sur <span class="font-semibold text-[#131b2e]">{{ $doctors->total() }}</span> médecins
                        </p>
                        <div class="sc-pager__pages">{{ $doctors->links() }}</div>
                    </div>

                    <div class="w-full bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-2 gap-2">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">pin_drop</span>
                                <span class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Localisation des cabinets</span>
                            </div>
                            <template x-if="current()?.maps">
                                <a :href="current().maps" target="_blank" rel="noopener noreferrer" class="text-[11px] text-[#004ac6] font-medium hover:underline">Ouvrir dans Maps</a>
                            </template>
                        </div>
                        <div class="w-full h-44 rounded-lg bg-gradient-to-br from-[#dbeafe] via-[#e0f2fe] to-[#eaedff] flex items-end p-2 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#283044]/70 via-[#283044]/20 to-transparent"></div>
                            <div class="relative z-10 text-white flex items-center justify-between w-full gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="material-symbols-outlined text-[18px] text-[#89f5e7]" aria-hidden="true">navigation</span>
                                    <span class="text-[11px] font-semibold truncate" x-text="current()?.address || 'Aucune adresse cartographique n’est branchée.'"></span>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-white/20 backdrop-blur-md text-[11px] font-semibold shrink-0">Aperçu</span>
                            </div>
                        </div>
                        <p class="mt-2 text-[12px] text-[#434655]">Aucune API cartographique n’est branchée. L’adresse réelle du médecin sélectionné reste affichée ici.</p>
                    </div>
                @endif
            </div>

            <aside class="lg:col-span-5 sticky top-24 space-y-4">
                <div class="w-full bg-white rounded-xl shadow-xl p-4 lg:p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#004ac6] to-[#006a61]"></div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-1 text-center text-[11px]">
                            <div class="flex flex-col items-center">
                                <span class="w-6 h-6 rounded-full bg-[#007d55] text-white flex items-center justify-center font-bold mb-1 shadow-sm">
                                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check</span>
                                </span>
                                <span class="text-[#434655] font-medium">1. Motif</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-[#006242] mx-1.5 mb-4"></div>
                            <div class="flex flex-col items-center">
                                <span class="w-6 h-6 rounded-full bg-[#004ac6] text-white flex items-center justify-center font-bold mb-1 shadow-md">2</span>
                                <span class="text-[#004ac6] font-bold">2. Date & heure</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-[#eaedff] mx-1.5 mb-4"></div>
                            <div class="flex flex-col items-center">
                                <span class="w-6 h-6 rounded-full bg-[#eaedff] text-[#434655] flex items-center justify-center font-semibold mb-1">3</span>
                                <span class="text-[#434655]">3. Confirmation</span>
                            </div>
                            <div class="h-0.5 flex-1 bg-[#eaedff] mx-1.5 mb-4"></div>
                            <div class="flex flex-col items-center">
                                <span class="w-6 h-6 rounded-full bg-[#eaedff] text-[#434655] flex items-center justify-center font-semibold mb-1">4</span>
                                <span class="text-[#434655]">4. Pass</span>
                            </div>
                        </div>
                    </div>

                    <template x-if="current()">
                        <div class="p-2 rounded-xl bg-[#f2f3ff] mb-4 flex items-center gap-2">
                            <div class="w-12 h-12 rounded-lg overflow-hidden shadow-sm shrink-0 bg-[#e2e7ff] flex items-center justify-center">
                                <template x-if="current().photo">
                                    <img :src="current().photo" :alt="'Photo de ' + current().name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!current().photo">
                                    <span class="text-[#004ac6] font-bold" x-text="current().name.charAt(0)"></span>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[11px] text-[#006242] font-bold tracking-wide uppercase">Réservation avec</span>
                                <h3 class="font-display text-[16px] leading-6 font-bold text-[#131b2e] truncate" x-text="'Dr. ' + current().name"></h3>
                                <p class="text-[12px] text-[#434655] truncate" x-text="current().specialty + ' • Consultation en cabinet'"></p>
                            </div>
                            <a :href="current().profile" class="p-1 rounded-md text-[#434655] hover:text-[#004ac6] hover:bg-[#eaedff]" title="Voir le profil">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">swap_horiz</span>
                            </a>
                        </div>
                    </template>

                    <div class="mb-4">
                        <p class="block text-[11px] text-[#434655] uppercase font-semibold mb-1">Format de consultation</p>
                        <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-[#eaedff]">
                            <button type="button" class="py-2 px-2 rounded-lg bg-white text-[#131b2e] shadow-sm text-[12px] font-bold inline-flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-[#004ac6]" aria-hidden="true">apartment</span>
                                Au cabinet
                            </button>
                            <button type="button" class="py-2 px-2 rounded-lg text-[#434655] text-[12px] font-medium inline-flex items-center justify-center gap-1.5"
                                    @click="toast('La téléconsultation n’est pas gérée.')">
                                <span class="material-symbols-outlined text-[16px] text-[#006a61]" aria-hidden="true">videocam</span>
                                Vidéo
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[11px] text-[#434655] uppercase font-semibold">Date de consultation</p>
                            <span class="text-[11px] text-[#131b2e] font-semibold" x-text="day()?.full || '—'"></span>
                        </div>
                        <div class="grid grid-cols-4 gap-1">
                            <template x-for="item in days()" :key="item.date">
                                <button type="button"
                                        class="flex flex-col items-center py-2 px-1 rounded-xl transition-colors"
                                        :class="selectedDay === item.date ? 'bg-[#004ac6] text-white shadow-sm' : 'bg-[#eaedff] text-[#131b2e] hover:bg-[#e2e7ff]'"
                                        @click="selectDay(item.date)">
                                    <span class="text-[11px] uppercase" :class="selectedDay === item.date ? 'opacity-80' : 'text-[#434655]'" x-text="item.isToday ? 'Aujourd’hui' : item.label"></span>
                                    <span class="font-display text-[16px] leading-6 font-bold" x-text="item.num"></span>
                                    <span class="text-[10px] font-medium" x-text="item.count + ' créneau' + (item.count > 1 ? 'x' : '')"></span>
                                </button>
                            </template>
                        </div>
                        <p class="mt-2 text-[12px] text-[#434655]" x-show="days().length === 0">Aucun créneau à venir pour ce médecin.</p>
                    </div>

                    <div class="mb-6 space-y-2" x-show="slots().length > 0">
                        <div x-show="morning().length > 0">
                            <span class="text-[11px] text-[#434655] font-semibold uppercase inline-flex items-center gap-1 mb-1.5">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">wb_sunny</span>
                                Matin
                            </span>
                            <div class="grid grid-cols-3 gap-1">
                                <template x-for="slot in morning()" :key="slot.id">
                                    <button type="button"
                                            class="py-2 px-2 rounded-lg text-[12px] font-semibold text-center"
                                            :class="selectedSlot === slot.id ? 'bg-[#004ac6] text-white shadow-md' : 'bg-[#eaedff] text-[#131b2e] hover:bg-[#e2e7ff]'"
                                            @click="selectSlot(slot.id)"
                                            x-text="slot.time"></button>
                                </template>
                            </div>
                        </div>
                        <div x-show="afternoon().length > 0">
                            <span class="text-[11px] text-[#434655] font-semibold uppercase inline-flex items-center gap-1 mb-1.5">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">wb_twilight</span>
                                Après-midi
                            </span>
                            <div class="grid grid-cols-3 gap-1">
                                <template x-for="slot in afternoon()" :key="slot.id">
                                    <button type="button"
                                            class="py-2 px-2 rounded-lg text-[12px] font-semibold text-center"
                                            :class="selectedSlot === slot.id ? 'bg-[#004ac6] text-white shadow-md' : 'bg-[#eaedff] text-[#131b2e] hover:bg-[#e2e7ff]'"
                                            @click="selectSlot(slot.id)"
                                            x-text="slot.time"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#f2f3ff] text-[15px]">
                            <div class="flex flex-col min-w-0">
                                <span class="text-[#131b2e] font-semibold truncate" x-text="'Sélection : ' + selectedLabel()"></span>
                                <span class="text-[#434655] text-[12px]">Présentiel • Confirmation après acceptation</span>
                            </div>
                        </div>
                        <button type="button"
                                class="w-full h-12 rounded-lg bg-[#004ac6] hover:bg-[#2563eb] text-white font-display text-[16px] font-semibold shadow-md inline-flex items-center justify-center gap-2"
                                @click="continueBooking()">
                            <span>Continuer vers la confirmation</span>
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                        </button>
                        <p class="pt-1 flex items-center justify-center gap-2 text-[#737686] text-[11px]">
                            <span class="material-symbols-outlined text-[#006242] text-[15px]" aria-hidden="true">lock</span>
                            <span>La demande reste en attente. Annulation possible pour un rendez-vous à venir.</span>
                        </p>
                    </div>
                </div>

                <div class="w-full bg-[#86f2e4]/30 rounded-xl p-4 shadow-sm flex items-start gap-2">
                    <span class="material-symbols-outlined text-[#006a61] text-[22px] shrink-0 mt-0.5" aria-hidden="true">contact_support</span>
                    <div class="flex-1">
                        <h2 class="font-display text-[16px] leading-6 text-[#006a61] font-bold">Besoin d’orientation ?</h2>
                        <p class="text-[12px] text-[#434655] mt-0.5">
                            SantéConnect n’ouvre pas de chat infirmier. Utilisez l’annuaire ou appelez le 15 / 112 en urgence.
                        </p>
                        <button type="button" class="inline-block mt-2 text-[12px] text-[#006a61] font-bold hover:underline"
                                @click="toast('Le chat infirmier n’est pas disponible.')">
                            Chat non disponible
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
