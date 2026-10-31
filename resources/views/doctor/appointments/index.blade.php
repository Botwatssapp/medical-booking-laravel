@php
    $stats = $stats ?? [
        'total' => $appointments->total(),
        'today' => 0,
        'pending' => 0,
        'accepted' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'missed' => 0,
        'rejected' => 0,
    ];
    $currentStatus = $status ?? request('status', '');
    $currentSearch = $search ?? request('search', '');
    $currentDate = $date ?? request('date', '');
    $currentDir = $direction ?? request('direction', 'desc');
    $filterQuery = function (?string $tab = null) use ($currentSearch, $currentDate, $currentDir) {
        return array_filter([
            'status' => $tab ?: null,
            'search' => $currentSearch !== '' ? $currentSearch : null,
            'date' => $currentDate !== '' ? $currentDate : null,
            'direction' => $currentDir !== 'desc' ? $currentDir : null,
        ]);
    };
    $bar = function (int $count) use ($stats): int {
        return $stats['total'] > 0 ? (int) round(($count * 100) / $stats['total']) : 0;
    };
    $labelFor = function (string $status): string {
        return match ($status) {
            'pending' => 'En attente',
            'accepted' => 'Confirmé',
            'rejected' => 'Refusé',
            'cancelled' => 'Annulé',
            'completed' => 'Terminé',
            'missed' => 'Non réalisé',
            default => ucfirst($status),
        };
    };
    $chipFor = function (string $status): string {
        return match ($status) {
            'pending' => 'bg-[#ffdad6] text-[#93000a]',
            'accepted' => 'bg-[#89f5e7] text-[#00201d]',
            'rejected' => 'bg-[#ffdad6] text-[#93000a]',
            'cancelled' => 'bg-[#e2e7ff] text-[#434655]',
            'completed' => 'bg-[#dae2fd] text-[#434655]',
            'missed' => 'bg-[#ffdad6] text-[#93000a]',
            default => 'bg-[#e2e7ff] text-[#434655]',
        };
    };
    $durationFor = function ($appointment): ?int {
        if (! $appointment->availability) {
            return null;
        }
        $start = \Carbon\Carbon::parse((string) $appointment->availability->start_time);
        $end = \Carbon\Carbon::parse((string) $appointment->availability->end_time);

        return max(0, $start->diffInMinutes($end));
    };
    $timeFor = function ($appointment) use ($durationFor): string {
        if ($appointment->availability) {
            $start = substr((string) $appointment->availability->start_time, 0, 5);
            $end = substr((string) $appointment->availability->end_time, 0, 5);
            $minutes = $durationFor($appointment);

            return $minutes ? $start.' – '.$end.' ('.$minutes.' min)' : $start.' – '.$end;
        }

        return $appointment->appointment_date->format('H:i');
    };
    $initialsFor = function ($name): string {
        return collect(preg_split('/\s+/', trim((string) $name)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    };
    $previewItems = $appointments->getCollection()->map(function ($appointment) use ($labelFor, $timeFor, $initialsFor) {
        $patient = $appointment->patient;

        return [
            'id' => $appointment->id,
            'name' => $patient->name,
            'initials' => $initialsFor($patient->name),
            'photo' => $patient->profile_image_url,
            'phone' => $patient->phone,
            'age' => $patient->age,
            'gender' => $patient->gender_label,
            'status' => $appointment->status,
            'statusLabel' => $labelFor($appointment->status),
            'time' => $timeFor($appointment),
            'date' => $appointment->appointment_date->format('d/m/Y'),
            'notes' => $appointment->notes,
            'showUrl' => route('doctor.appointments.show', $appointment),
        ];
    })->values();
    $tabs = [
        ['val' => '', 'label' => 'Tous', 'count' => $stats['total']],
        ['val' => 'today', 'label' => "Aujourd'hui", 'count' => $stats['today']],
        ['val' => 'pending', 'label' => 'En attente', 'count' => $stats['pending']],
        ['val' => 'accepted', 'label' => 'Confirmés', 'count' => $stats['accepted']],
        ['val' => 'completed', 'label' => 'Terminés', 'count' => $stats['completed']],
        ['val' => 'cancelled', 'label' => 'Annulés', 'count' => $stats['cancelled']],
        ['val' => 'rejected', 'label' => 'Refusés', 'count' => $stats['rejected']],
        ['val' => 'missed', 'label' => 'Non réalisés', 'count' => $stats['missed']],
    ];
@endphp

@extends('layouts.doctor')
@section('page-title', 'Mes rendez-vous')
@section('page-subtitle', 'Consultations et demandes patients')

@section('doctor-content')
<div class="sc-stack w-full"
     x-data="{
        selectedId: {{ $appointments->first()?->id ?? 'null' }},
        items: @js($previewItems),
        exportOpen: false,
        toast(message) {
            if (window.Alpine && Alpine.$data(document.body) && typeof Alpine.$data(document.body).showToast === 'function') {
                Alpine.$data(document.body).showToast(message);
            }
        },
        get selected() {
            return this.items.find((item) => item.id === this.selectedId) || this.items[0] || null;
        }
     }">

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <nav aria-label="Fil d'Ariane" class="flex items-center gap-1.5 text-[#434655] text-[12px]">
                <span>Espace Praticien</span>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span class="text-[#004ac6] font-semibold">Mes rendez-vous</span>
            </nav>
            <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 tracking-tight">Mes rendez-vous</h2>
            <p class="text-[15px] leading-6 text-[#434655] max-w-2xl">
                Gérez vos consultations du jour, vos demandes en attente et l’historique de vos rendez-vous.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 self-start lg:self-center">
            <div class="relative">
                <button type="button"
                        class="h-11 px-4 bg-white text-[#131b2e] text-[14px] font-semibold rounded-lg shadow-sm hover:bg-[#f2f3ff] inline-flex items-center gap-1.5"
                        @click="exportOpen = !exportOpen"
                        @click.outside="exportOpen = false">
                    <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">calendar_month</span>
                    <span>Exporter l’agenda</span>
                    <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">expand_more</span>
                </button>
                <div x-show="exportOpen" x-cloak x-transition
                     class="absolute right-0 mt-1.5 w-56 bg-white rounded-xl shadow-xl p-1 z-30 flex flex-col">
                    <button type="button" class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-[13px] text-left hover:bg-[#f2f3ff]"
                            @click="exportOpen = false; toast('L’export iCal n’est pas géré.')">
                        <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">download</span>
                        Fichier calendrier
                    </button>
                    <button type="button" class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-[13px] text-left hover:bg-[#f2f3ff]"
                            @click="exportOpen = false; toast('L’export CSV n’est pas géré.')">
                        <span class="material-symbols-outlined text-[#007d55] text-[18px]" aria-hidden="true">table_view</span>
                        Fichier CSV
                    </button>
                </div>
            </div>
            <button type="button"
                    class="h-11 px-4 bg-[#ffdad6] text-[#93000a] hover:bg-[#ba1a1a] hover:text-white text-[14px] font-semibold rounded-lg shadow-sm inline-flex items-center gap-1.5"
                    @click="toast('L’indisponibilité urgente n’est pas gérée — supprimez les créneaux libres.')">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">event_busy</span>
                <span>Définir une indisponibilité urgente</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="{{ route('doctor.appointments.index', $filterQuery('today')) }}"
           class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between {{ $currentStatus === 'today' ? 'ring-2 ring-[#004ac6] bg-[#f2f3ff]' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-semibold text-[#737686] tracking-wider">Aujourd’hui</span>
                <span class="w-8 h-8 rounded-lg bg-[#dbe1ff] flex items-center justify-center text-[#004ac6]">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">today</span>
                </span>
            </div>
            <div class="mt-2">
                <div class="font-display text-[32px] leading-tight font-bold">{{ $stats['today'] }}</div>
                <p class="text-[13px] text-[#434655] mt-1">Rendez-vous du jour</p>
            </div>
            <div class="w-full bg-[#eaedff] rounded-full h-1.5 mt-4 overflow-hidden">
                <div class="bg-[#004ac6] h-full rounded-full" style="width: {{ $bar($stats['today']) }}%"></div>
            </div>
        </a>

        <a href="{{ route('doctor.appointments.index', $filterQuery('pending')) }}"
           class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between {{ $currentStatus === 'pending' ? 'ring-2 ring-[#006a61] bg-[#f2f3ff]' : '' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] uppercase font-semibold text-[#737686] tracking-wider">En attente</span>
                    @if($stats['pending'] > 0)
                        <span class="w-2 h-2 rounded-full bg-[#ba1a1a] animate-ping"></span>
                    @endif
                </div>
                <span class="w-8 h-8 rounded-lg bg-[#ffdad6] text-[#93000a] flex items-center justify-center font-bold text-[11px]">{{ $stats['pending'] }}</span>
            </div>
            <div class="mt-2">
                <div class="font-display text-[32px] leading-tight font-bold {{ $stats['pending'] > 0 ? 'text-[#ba1a1a]' : '' }}">{{ $stats['pending'] }}</div>
                <p class="text-[13px] {{ $stats['pending'] > 0 ? 'text-[#ba1a1a]' : 'text-[#434655]' }} mt-1 flex items-center gap-1">
                    @if($stats['pending'] > 0)
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">priority_high</span>
                    @endif
                    Nécessitent validation
                </p>
            </div>
            <div class="w-full bg-[#eaedff] rounded-full h-1.5 mt-4 overflow-hidden">
                <div class="bg-[#ba1a1a] h-full rounded-full" style="width: {{ $bar($stats['pending']) }}%"></div>
            </div>
        </a>

        <a href="{{ route('doctor.appointments.index', $filterQuery('accepted')) }}"
           class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between {{ $currentStatus === 'accepted' ? 'ring-2 ring-[#007d55] bg-[#f2f3ff]' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-semibold text-[#737686] tracking-wider">Confirmés</span>
                <span class="w-8 h-8 rounded-lg bg-[#e2e7ff] text-[#007d55] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">check_circle</span>
                </span>
            </div>
            <div class="mt-2">
                <div class="font-display text-[32px] leading-tight font-bold">{{ $stats['accepted'] }}</div>
                <p class="text-[13px] text-[#434655] mt-1">Rendez-vous acceptés</p>
            </div>
            <div class="w-full bg-[#eaedff] rounded-full h-1.5 mt-4 overflow-hidden">
                <div class="bg-[#007d55] h-full rounded-full" style="width: {{ $bar($stats['accepted']) }}%"></div>
            </div>
        </a>

        <a href="{{ route('doctor.appointments.index', $filterQuery('completed')) }}"
           class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between {{ $currentStatus === 'completed' ? 'ring-2 ring-[#2563eb] bg-[#f2f3ff]' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-semibold text-[#737686] tracking-wider">Honorés</span>
                <span class="w-8 h-8 rounded-lg bg-[#f2f3ff] text-[#434655] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">task_alt</span>
                </span>
            </div>
            <div class="mt-2">
                <div class="font-display text-[32px] leading-tight font-bold">{{ $stats['completed'] }}</div>
                <p class="text-[13px] text-[#434655] mt-1">Consultations terminées</p>
            </div>
            <div class="w-full bg-[#eaedff] rounded-full h-1.5 mt-4 overflow-hidden">
                <div class="bg-[#737686] h-full rounded-full" style="width: {{ $bar($stats['completed']) }}%"></div>
            </div>
        </a>

        <a href="{{ route('doctor.appointments.index', $filterQuery('cancelled')) }}"
           class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between {{ in_array($currentStatus, ['cancelled', 'missed'], true) ? 'ring-2 ring-[#737686] bg-[#f2f3ff]' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase font-semibold text-[#737686] tracking-wider">Annulés / Absent</span>
                <span class="w-8 h-8 rounded-lg bg-[#f2f3ff] text-[#737686] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person_off</span>
                </span>
            </div>
            <div class="mt-2">
                <div class="font-display text-[32px] leading-tight font-bold text-[#434655]">{{ $stats['cancelled'] + $stats['missed'] }}</div>
                <p class="text-[13px] text-[#434655] mt-1">{{ $stats['cancelled'] }} annulé{{ $stats['cancelled'] > 1 ? 's' : '' }} • {{ $stats['missed'] }} non réalisé{{ $stats['missed'] > 1 ? 's' : '' }}</p>
            </div>
            <div class="w-full bg-[#eaedff] rounded-full h-1.5 mt-4 overflow-hidden">
                <div class="bg-[#c3c6d7] h-full rounded-full" style="width: {{ $bar($stats['cancelled'] + $stats['missed']) }}%"></div>
            </div>
        </a>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm sc-toolbar">
        <form method="GET" action="{{ route('doctor.appointments.index') }}" class="flex flex-col sm:flex-row items-stretch gap-2 w-full">
            @if($currentStatus !== '')
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            @if($currentDate !== '')
                <input type="hidden" name="date" value="{{ $currentDate }}">
            @endif
            @if($currentDir !== 'desc')
                <input type="hidden" name="direction" value="{{ $currentDir }}">
            @endif
            <div class="relative flex-1 min-w-0">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#737686] text-[20px]" aria-hidden="true">search</span>
                <input class="w-full h-11 pl-11 pr-4 rounded-xl bg-[#f2f3ff] text-[13px] text-[#131b2e] placeholder:text-[#737686] focus:outline-none focus:ring-2 focus:ring-[#004ac6]"
                       type="search"
                       name="search"
                       value="{{ $currentSearch }}"
                       placeholder="Rechercher un patient par nom…">
            </div>
            <button type="submit" class="h-11 px-4 rounded-xl bg-[#004ac6] text-white text-[12px] font-semibold shrink-0">
                Rechercher
            </button>
        </form>

        <div class="sc-scroll-row bg-[#f2f3ff] p-1 rounded-xl" role="navigation" aria-label="Filtrer par statut">
            @foreach($tabs as $tab)
                <a href="{{ route('doctor.appointments.index', $filterQuery($tab['val'] !== '' ? $tab['val'] : null)) }}"
                   class="px-3 py-2 rounded-lg text-[12px] font-semibold whitespace-nowrap transition-all {{ $currentStatus === $tab['val'] ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655] hover:text-[#131b2e]' }}"
                   @if($currentStatus === $tab['val']) aria-current="page" @endif>
                    {{ $tab['label'] }} ({{ $tab['count'] }})
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('doctor.appointments.index') }}" class="flex items-center gap-2">
            @if($currentStatus !== '')
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            @if($currentSearch !== '')
                <input type="hidden" name="search" value="{{ $currentSearch }}">
            @endif
            <label class="sr-only" for="doctor-appointment-date">Filtrer par date</label>
            <input id="doctor-appointment-date"
                   class="h-11 px-3 bg-[#f2f3ff] text-[#131b2e] text-[12px] font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-[#004ac6]"
                   type="date"
                   name="date"
                   value="{{ $currentDate }}"
                   onchange="this.form.submit()">
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-7 flex flex-col gap-4">
            @forelse($appointments as $apt)
                @php
                    $patient = $apt->patient;
                    $faded = in_array($apt->status, ['cancelled', 'rejected', 'missed', 'completed'], true);
                    $photo = $patient->profile_image_url;
                @endphp
                <article @click="selectedId = {{ $apt->id }}"
                         :class="selectedId === {{ $apt->id }} ? 'ring-2 ring-[#004ac6] bg-[#dbe1ff]/10' : ''"
                         class="bg-white p-4 rounded-2xl shadow-sm hover:shadow-md transition-all cursor-pointer {{ $faded ? 'opacity-90' : '' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="relative shrink-0">
                                @if($photo)
                                    <img class="w-14 h-14 rounded-2xl object-cover shadow-sm ring-2 ring-[#004ac6]/20" src="{{ $photo }}" alt="Photo de {{ $patient->name }}">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-[#dbe1ff] text-[#004ac6] flex items-center justify-center font-bold">{{ $initialsFor($patient->name) }}</div>
                                @endif
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h3 class="text-[16px] leading-6 text-[#131b2e] font-bold">{{ $patient->name }}</h3>
                                    <span class="px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[11px] text-[#434655] font-mono">#{{ $apt->id }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full {{ $chipFor($apt->status) }} text-[11px] font-semibold">{{ $labelFor($apt->status) }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-[#434655] text-[13px] mt-1 flex-wrap">
                                    <span class="flex items-center gap-1 text-[#004ac6] font-semibold">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">schedule</span>
                                        {{ $timeFor($apt) }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $apt->appointment_date->format('d/m/Y') }}</span>
                                </div>
                                @if($apt->notes)
                                    <div class="mt-1.5 text-[13px] bg-[#f2f3ff] px-3 py-1.5 rounded-lg flex items-start gap-2">
                                        <span class="material-symbols-outlined text-[#737686] text-[16px] mt-0.5" aria-hidden="true">stethoscope</span>
                                        <span class="font-medium truncate max-w-sm">{{ $apt->notes }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap sm:flex-col items-stretch sm:items-end justify-end gap-2 pt-1 sm:pt-0" @click.stop>
                            @if($apt->status === 'pending')
                                <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="h-9 px-4 bg-[#006242] text-white hover:bg-[#007d55] text-[12px] font-semibold rounded-lg shadow-sm inline-flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">check</span>
                                        Accepter le RDV
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}"
                                      onsubmit="return confirm('Refuser la demande de {{ addslashes($patient->name) }} ?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="h-9 px-4 bg-[#f2f3ff] text-[#ba1a1a] hover:bg-[#ffdad6] text-[12px] font-semibold rounded-lg inline-flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">close</span>
                                        Refuser
                                    </button>
                                </form>
                            @elseif($apt->status === 'accepted')
                                <form method="POST" action="{{ route('doctor.appointments.update', $apt) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="h-9 px-4 bg-[#2563eb] text-white hover:bg-[#004ac6] text-[12px] font-semibold rounded-lg shadow-sm inline-flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">task_alt</span>
                                        Terminé
                                    </button>
                                </form>
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    <a href="{{ route('doctor.appointments.show', $apt) }}"
                                       class="h-9 px-3 bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold rounded-lg inline-flex items-center gap-1">
                                        Détails
                                    </a>
                                    <form method="POST" action="{{ route('doctor.appointments.reschedule', $apt) }}"
                                          onsubmit="return confirm('Reporter {{ addslashes($patient->name) }} ({{ $apt->appointment_date->format('d/m/Y H:i') }}) sur votre prochain créneau libre ?')">
                                        @csrf
                                        <button type="submit" class="h-9 px-3 text-[#737686] hover:text-[#131b2e] text-[12px] font-semibold rounded-lg inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">update</span>
                                            Reporter
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('doctor.appointments.destroy', $apt) }}"
                                          onsubmit="return confirm('Annuler le rendez-vous de {{ addslashes($patient->name) }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-[#737686] hover:text-[#ba1a1a] hover:bg-[#ffdad6]" title="Annuler" aria-label="Annuler le rendez-vous">
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('doctor.appointments.show', $apt) }}"
                                   class="h-9 px-4 bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold rounded-lg inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">info</span>
                                    Détails
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-2xl py-16 text-center shadow-sm">
                    <span class="material-symbols-outlined text-6xl text-[#c3c6d7] block mb-4" aria-hidden="true">calendar_today</span>
                    @if($currentStatus !== '' || $currentSearch !== '' || $currentDate !== '')
                        <p class="font-semibold text-[#131b2e]">Aucun rendez-vous pour ce filtre</p>
                        <p class="text-sm text-[#434655] mt-1 mb-4">Essayez un autre statut ou une autre recherche.</p>
                        <a href="{{ route('doctor.appointments.index') }}" class="text-sm font-semibold text-[#004ac6] hover:underline">Voir tous les rendez-vous</a>
                    @else
                        <p class="font-semibold text-[#131b2e]">Aucun rendez-vous pour le moment</p>
                        <p class="text-sm text-[#434655] mt-1 mb-4">Vos demandes apparaîtront ici dès qu’un patient réservera un créneau.</p>
                        <a href="{{ route('doctor.availabilities.create') }}" class="inline-flex items-center px-4 py-2 bg-[#2563eb] text-white rounded-xl text-sm font-semibold">Créer une disponibilité</a>
                    @endif
                </div>
            @endforelse

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

        <div class="lg:col-span-5 sticky top-24 flex flex-col gap-4">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden" x-show="selected">
                <div class="bg-[#f2f3ff] p-4 flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <template x-if="selected && selected.photo">
                            <img class="w-12 h-12 rounded-xl object-cover" :src="selected.photo" :alt="'Photo de ' + selected.name">
                        </template>
                        <template x-if="selected && !selected.photo">
                            <div class="w-12 h-12 rounded-xl bg-[#dbe1ff] text-[#004ac6] flex items-center justify-center font-bold" x-text="selected.initials"></div>
                        </template>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[16px] leading-6 font-bold truncate" x-text="selected ? selected.name : ''"></h3>
                                <span class="font-mono text-[11px] font-semibold text-[#004ac6] px-2 py-0.5 rounded-full bg-[#dbe1ff]" x-text="selected ? '#' + selected.id : ''"></span>
                            </div>
                            <p class="text-[13px] text-[#434655] mt-0.5 flex items-center gap-2 flex-wrap">
                                <span x-show="selected && selected.age" x-text="selected && selected.age ? selected.age + ' ans' : ''"></span>
                                <span x-show="selected && selected.age && selected.gender">•</span>
                                <span x-show="selected && selected.gender" x-text="selected ? selected.gender : ''"></span>
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold shrink-0"
                          :class="{
                              'bg-[#89f5e7] text-[#00201d]': selected && selected.status === 'accepted',
                              'bg-[#ffdad6] text-[#93000a]': selected && (selected.status === 'pending' || selected.status === 'rejected' || selected.status === 'missed'),
                              'bg-[#dae2fd] text-[#434655]': selected && (selected.status === 'completed' || selected.status === 'cancelled')
                          }"
                          x-text="selected ? selected.statusLabel : ''"></span>
                </div>

                <div class="px-4 py-3 border-b border-[#f2f3ff] flex items-center justify-between text-[13px]" x-show="selected && selected.phone">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">call</span>
                        <span class="font-medium" x-text="selected ? selected.phone : ''"></span>
                    </div>
                </div>

                <div class="p-4 flex flex-col gap-4">
                    <div class="p-3 rounded-xl bg-[#f2f3ff] flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] text-[#737686] uppercase font-semibold">
                            <span>Détails consultation</span>
                            <span class="text-[#004ac6] font-bold normal-case" x-text="selected ? selected.time : ''"></span>
                        </div>
                        <div class="text-[16px] leading-6 font-semibold" x-text="selected && selected.notes ? selected.notes : 'Aucun motif renseigné'"></div>
                        <div class="flex items-center gap-2 text-[13px] text-[#434655]">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">event</span>
                            <span x-text="selected ? selected.date : ''"></span>
                        </div>
                    </div>

                    <a :href="selected ? selected.showUrl : '#'"
                       class="w-full h-11 bg-[#2563eb] text-white hover:bg-[#004ac6] text-[14px] font-semibold rounded-xl shadow-sm inline-flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">info</span>
                        Ouvrir la fiche
                    </a>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                class="h-10 bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold rounded-xl inline-flex items-center justify-center gap-1.5"
                                @click="toast('La rédaction de compte-rendu n’est pas gérée.')">
                            <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">edit_note</span>
                            Compte-rendu
                        </button>
                        <button type="button"
                                class="h-10 bg-[#f2f3ff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold rounded-xl inline-flex items-center justify-center gap-1.5"
                                @click="toast('L’ordonnance n’est pas générée.')">
                            <span class="material-symbols-outlined text-[#006a61] text-[18px]" aria-hidden="true">medication</span>
                            Ordonnance
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-[#86f2e4]/20 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-[#006a61] text-white flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">event_available</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="text-[12px] font-bold">Gérer les disponibilités</span>
                        <span class="text-[13px] text-[#434655]">Ouvrez ou fermez vos créneaux de réservation.</span>
                    </div>
                </div>
                <a href="{{ route('doctor.availabilities.index') }}"
                   class="px-3 py-1.5 rounded-lg bg-white text-[11px] font-semibold text-[#006a61] hover:bg-[#f2f3ff]">
                    Configurer
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
