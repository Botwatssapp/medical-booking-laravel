@extends('layouts.doctor')
@section('page-title', 'Agenda')
@section('page-subtitle', $selectedDate->translatedFormat('l j F Y'))

@section('page-actions')
    @if(Auth::user()->hasDoctorProfile())
        <a href="{{ route('doctor.availabilities.create') }}"
           class="inline-flex items-center gap-1.5 h-10 px-4 rounded-lg bg-[#004ac6] text-white text-[14px] font-semibold shadow-sm hover:bg-[#2563eb]">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
            Nouveau créneau
        </a>
    @endif
@endsection

@section('doctor-content')
@php
    $user = Auth::user();
    $doctor = $user->doctor;
    $photo = $user->profile_image_url
        ?? ($doctor?->photo ? asset('storage/'.$doctor->photo) : null);
    $agendaView = $agendaView ?? 'day';
    $agendaUrl = function (?string $date = null, ?string $view = null) use ($selectedDate, $agendaView) {
        $params = ['date' => $date ?? $selectedDate->toDateString()];
        $view ??= $agendaView;
        if ($view !== 'day') {
            $params['view'] = $view;
        }

        return route('doctor.dashboard', $params);
    };
    $prevDate = match ($agendaView) {
        'week' => $selectedDate->copy()->subWeek()->toDateString(),
        'month' => $selectedDate->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        default => $selectedDate->copy()->subDay()->toDateString(),
    };
    $nextDate = match ($agendaView) {
        'week' => $selectedDate->copy()->addWeek()->toDateString(),
        'month' => $selectedDate->copy()->addMonthNoOverflow()->startOfMonth()->toDateString(),
        default => $selectedDate->copy()->addDay()->toDateString(),
    };
    $prevLabel = match ($agendaView) {
        'week' => 'Semaine précédente',
        'month' => 'Mois précédent',
        default => 'Jour précédent',
    };
    $nextLabel = match ($agendaView) {
        'week' => 'Semaine suivante',
        'month' => 'Mois suivant',
        default => 'Jour suivant',
    };
    $agendaLabel = match ($agendaView) {
        'week' => $weekStart->translatedFormat('d M').' – '.$weekEnd->translatedFormat('d M Y'),
        'month' => $selectedDate->translatedFormat('F Y'),
        default => ($selectedDate->isToday() ? 'Aujourd’hui, ' : '').$selectedDate->translatedFormat('D j M Y'),
    };
    $tabOn = 'px-3 py-1.5 rounded-lg bg-white text-[11px] text-[#004ac6] shadow-sm font-semibold';
    $tabOff = 'px-3 py-1.5 rounded-lg text-[11px] text-[#434655] hover:text-[#004ac6]';
@endphp

<div class="sc-stack w-full">

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

        <section class="bg-white p-6 rounded-xl shadow-sm flex flex-col xl:flex-row xl:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <div class="w-14 h-14 rounded-full overflow-hidden shadow-sm ring-2 ring-[#2563eb] bg-[#e2e7ff] text-[#004ac6] flex items-center justify-center font-bold">
                            @if($photo)
                                <img src="{{ $photo }}" alt="Photo de Dr. {{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <span aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full bg-[#007d55] ring-2 ring-white"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="font-display text-[20px] leading-7 font-semibold text-[#131b2e]">Dr. {{ $user->name }}</h1>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[#004ac6] font-semibold">{{ $doctor->speciality->name }}</span>
                        </div>
                        <p class="text-[13px] text-[#434655]">{{ $doctor->address ?: 'Adresse du cabinet non renseignée.' }}</p>
                    </div>
                </div>
                <div class="h-8 w-px bg-[#e2e7ff] hidden sm:block"></div>
                <div class="inline-flex items-center p-1 rounded-full bg-[#eaedff] gap-1" x-data="{ mode: 'available' }">
                    <button type="button"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold transition-all"
                            :class="mode === 'available' ? 'bg-white shadow-sm text-[#006242]' : 'text-[#434655]'"
                            @click="mode = 'available'">
                        <span class="w-2 h-2 rounded-full bg-[#007d55] animate-pulse"></span>
                        Disponible
                    </button>
                    <button type="button"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-full text-[12px] font-semibold transition-all"
                            :class="mode === 'busy' ? 'bg-white shadow-sm text-[#006a61]' : 'text-[#434655]'"
                            @click="mode = 'busy'">
                        En consultation
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 justify-between xl:justify-end">
                <div class="flex items-center gap-1 bg-[#f2f3ff] px-4 py-1.5 rounded-lg shadow-sm">
                    <a href="{{ $agendaUrl($prevDate) }}" class="p-1 rounded hover:bg-[#e2e7ff] text-[#434655]" aria-label="{{ $prevLabel }}">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_left</span>
                    </a>
                    <div class="flex items-center gap-1.5 px-2">
                        <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">calendar_today</span>
                        <span class="text-[14px] font-semibold text-[#131b2e] whitespace-nowrap">{{ $agendaLabel }}</span>
                    </div>
                    <a href="{{ $agendaUrl($nextDate) }}" class="p-1 rounded hover:bg-[#e2e7ff] text-[#434655]" aria-label="{{ $nextLabel }}">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>
                    </a>
                </div>
                <div class="inline-flex rounded-lg bg-[#eaedff] p-0.5" role="tablist" aria-label="Période de l’agenda">
                    <a href="{{ $agendaUrl($selectedDate->toDateString(), 'day') }}"
                       class="{{ $agendaView === 'day' ? $tabOn : $tabOff }}"
                       role="tab"
                       @if($agendaView === 'day') aria-current="page" @endif>Jour</a>
                    <a href="{{ $agendaUrl($selectedDate->toDateString(), 'week') }}"
                       class="{{ $agendaView === 'week' ? $tabOn : $tabOff }}"
                       role="tab"
                       @if($agendaView === 'week') aria-current="page" @endif>Semaine</a>
                    <a href="{{ $agendaUrl($selectedDate->toDateString(), 'month') }}"
                       class="{{ $agendaView === 'month' ? $tabOn : $tabOff }}"
                       role="tab"
                       @if($agendaView === 'month') aria-current="page" @endif>Mois</a>
                </div>
                <a href="{{ route('doctor.availabilities.create') }}"
                   class="h-10 px-4 rounded-lg bg-[#004ac6] text-white text-[14px] font-semibold inline-flex items-center gap-1.5 shadow-sm hover:bg-[#2563eb]">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add</span>
                    Ajouter des créneaux
                </a>
            </div>
        </section>

        <section class="sc-kpi-grid">
            <div class="bg-white p-4 rounded-xl shadow-sm">
                <div class="flex items-center justify-between text-[#434655] mb-1">
                    <span class="text-[12px] font-semibold uppercase tracking-wider">Consultations du jour</span>
                    <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">ecg_heart</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display text-[28px] leading-9 font-bold text-[#131b2e]">{{ $todayVisits }}</span>
                    <span class="text-[13px] text-[#434655]">patient(s)</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                    <span class="px-1.5 py-0.5 rounded text-[11px] font-semibold bg-[#6ffbbe] text-[#002113]">{{ $todayDone }} terminé(s)</span>
                    <span class="px-1.5 py-0.5 rounded text-[11px] font-semibold bg-[#89f5e7] text-[#00201d]">{{ $todayActive }} en cours</span>
                    <span class="px-1.5 py-0.5 rounded text-[11px] font-semibold bg-[#eaedff] text-[#434655]">{{ $todayNext }} à venir</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm">
                <div class="flex items-center justify-between text-[#434655] mb-1">
                    <span class="text-[12px] font-semibold uppercase tracking-wider">Demandes en attente</span>
                    <span class="material-symbols-outlined text-[#006a61] text-[20px]" aria-hidden="true">notification_important</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display text-[28px] leading-9 font-bold text-[#004ac6]">{{ $pendingAppointments }}</span>
                    <span class="text-[13px] text-[#006a61] font-medium">à traiter</span>
                </div>
                <p class="text-[13px] text-[#434655] mt-2">Aucun délai de réponse n’est calculé ici.</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm">
                <div class="flex items-center justify-between text-[#434655] mb-1">
                    <span class="text-[12px] font-semibold uppercase tracking-wider">Volume du mois</span>
                    <span class="material-symbols-outlined text-[#006242] text-[20px]" aria-hidden="true">groups</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display text-[28px] leading-9 font-bold text-[#131b2e]">{{ $monthCaseload }}</span>
                </div>
                <p class="text-[13px] text-[#434655] mt-2">Rendez-vous occupants de {{ $selectedDate->translatedFormat('F Y') }}.</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm">
                <div class="flex items-center justify-between text-[#434655] mb-1">
                    <span class="text-[12px] font-semibold uppercase tracking-wider">Avis patients</span>
                    <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">hotel_class</span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display text-[28px] leading-9 font-bold text-[#131b2e]">—</span>
                </div>
                <p class="text-[13px] text-[#434655] mt-2">Aucun avis n’est enregistré. SantéConnect n’affiche pas de note fictive.</p>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 w-full items-start">
            <section class="lg:col-span-8 flex flex-col gap-4">
                @if($activeNow)
                    <div class="bg-gradient-to-r from-[#dae2fd] via-white to-white p-6 rounded-xl shadow-sm flex flex-col md:flex-row justify-between md:items-center gap-4 relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-44 h-44 rounded-full bg-[#004ac6]/5 pointer-events-none"></div>
                        <div class="flex items-start gap-4 z-10">
                            <div class="w-12 h-12 rounded-xl bg-[#004ac6] text-white flex items-center justify-center shadow-md">
                                <span class="material-symbols-outlined text-[28px]" aria-hidden="true">stethoscope</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#007d55] animate-ping"></span>
                                    <span class="text-[11px] text-[#006242] uppercase tracking-wider font-bold">
                                        En consultation
                                        @if($activeNow->availability)
                                            ({{ substr($activeNow->availability->start_time, 0, 5) }} – {{ substr($activeNow->availability->end_time, 0, 5) }})
                                        @endif
                                    </span>
                                </div>
                                <h2 class="font-display text-[20px] leading-7 font-semibold text-[#131b2e] mt-0.5">{{ $activeNow->patient->name }}</h2>
                                <p class="text-[13px] text-[#434655]">{{ $activeNow->notes ?: 'Aucune note.' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('doctor.appointments.show', $activeNow) }}"
                           class="z-10 h-10 px-4 rounded-lg bg-white text-[#004ac6] shadow-sm text-[12px] font-semibold inline-flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">clinical_notes</span>
                            Détail
                        </a>
                    </div>
                @elseif($nextAppointment)
                    <div class="bg-gradient-to-r from-[#dae2fd] via-white to-white p-6 rounded-xl shadow-sm flex flex-col md:flex-row justify-between md:items-center gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#004ac6] text-white flex items-center justify-center shadow-md">
                                <span class="material-symbols-outlined text-[28px]" aria-hidden="true">stethoscope</span>
                            </div>
                            <div>
                                <p class="text-[11px] text-[#006242] uppercase tracking-wider font-bold">Prochain rendez-vous</p>
                                <h2 class="font-display text-[20px] leading-7 font-semibold text-[#131b2e] mt-0.5">{{ $nextAppointment->patient->name }}</h2>
                                <p class="text-[13px] text-[#434655]">
                                    {{ $nextAppointment->appointment_date->format('d/m/Y') }}
                                    @if($nextAppointment->availability)
                                        · {{ substr($nextAppointment->availability->start_time, 0, 5) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('doctor.appointments.show', $nextAppointment) }}"
                           class="h-10 px-4 rounded-lg bg-white text-[#004ac6] shadow-sm text-[12px] font-semibold inline-flex items-center gap-1.5">
                            Voir le détail
                        </a>
                    </div>
                @endif

                <div class="bg-white p-6 rounded-xl shadow-sm flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-2">
                        <div>
                            @if($agendaView === 'week')
                                <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Planning clinique de la semaine</h3>
                                <p class="text-[13px] text-[#434655]">{{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M Y') }}</p>
                            @elseif($agendaView === 'month')
                                <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Planning clinique du mois</h3>
                                <p class="text-[13px] text-[#434655]">{{ $selectedDate->translatedFormat('F Y') }}</p>
                            @else
                                <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Planning clinique du jour</h3>
                                <p class="text-[13px] text-[#434655]">{{ $selectedDate->translatedFormat('l j F') }}</p>
                            @endif
                        </div>
                    </div>

                    @if($agendaView === 'week')
                        <div class="space-y-3">
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $day = $weekStart->copy()->addDays($i);
                                    $dayKey = $day->toDateString();
                                    $dayItems = $weekAppointments->get($dayKey, collect());
                                @endphp
                                <div class="rounded-xl border border-[#e2e7ff] overflow-hidden">
                                    <a href="{{ $agendaUrl($dayKey, 'day') }}"
                                       class="flex items-center justify-between px-4 py-2.5 bg-[#f2f3ff] hover:bg-[#eaedff]">
                                        <span class="text-[13px] font-semibold text-[#131b2e]">{{ $day->translatedFormat('l j F') }}</span>
                                        <span class="text-[11px] text-[#434655]">{{ $dayItems->count() }} RDV</span>
                                    </a>
                                    @forelse($dayItems as $apt)
                                        <div class="flex items-center justify-between gap-3 px-4 py-3 border-t border-[#eaedff]">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="text-[14px] font-semibold text-[#131b2e] truncate">{{ $apt->patient->name }}</p>
                                                    @include('doctor.partials.status-badge', ['appointment' => $apt])
                                                </div>
                                                <p class="text-[12px] text-[#434655] mt-0.5">
                                                    @if($apt->availability)
                                                        {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                                                    @else
                                                        {{ $apt->appointment_date->format('H:i') }}
                                                    @endif
                                                </p>
                                            </div>
                                            <a href="{{ route('doctor.appointments.show', $apt) }}"
                                               class="h-9 px-3 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[12px] font-semibold inline-flex items-center shrink-0">
                                                Détail
                                            </a>
                                        </div>
                                    @empty
                                        <p class="px-4 py-3 text-[13px] text-[#434655] border-t border-[#eaedff]">Aucun rendez-vous.</p>
                                    @endforelse
                                </div>
                            @endfor
                        </div>
                    @elseif($agendaView === 'month')
                        @php
                            $monthStart = $selectedDate->copy()->startOfMonth();
                            $daysInMonth = $monthStart->daysInMonth;
                            $pad = $monthStart->isoWeekday() - 1;
                        @endphp
                        <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-[#434655] mb-1">
                            @foreach(['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $weekday)
                                <span>{{ $weekday }}</span>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            @for($i = 0; $i < $pad; $i++)
                                <div class="min-h-14 rounded-lg bg-[#faf8ff]" aria-hidden="true"></div>
                            @endfor
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $cell = $monthStart->copy()->day($d);
                                    $cellKey = $cell->toDateString();
                                    $cellCount = $monthAppointments->get($cellKey, collect())->count();
                                    $isSelected = $cell->isSameDay($selectedDate);
                                @endphp
                                <a href="{{ $agendaUrl($cellKey, 'day') }}"
                                   class="min-h-14 rounded-lg p-1.5 flex flex-col items-center justify-center text-[12px] {{ $isSelected ? 'bg-[#004ac6] text-white' : ($cellCount ? 'bg-[#e2e7ff] text-[#00174b]' : 'bg-[#f2f3ff] text-[#434655]') }}">
                                    <span class="font-semibold">{{ $d }}</span>
                                    @if($cellCount)
                                        <span class="text-[10px] {{ $isSelected ? 'text-white/90' : 'text-[#004ac6]' }}">{{ $cellCount }} RDV</span>
                                    @endif
                                </a>
                            @endfor
                        </div>
                        <div class="space-y-2 pt-2">
                            @forelse($monthAppointments as $dateKey => $dayItems)
                                <div class="rounded-xl border border-[#e2e7ff] overflow-hidden">
                                    <a href="{{ $agendaUrl($dateKey, 'day') }}"
                                       class="flex items-center justify-between px-4 py-2.5 bg-[#f2f3ff] hover:bg-[#eaedff]">
                                        <span class="text-[13px] font-semibold text-[#131b2e]">{{ \Carbon\Carbon::parse($dateKey)->translatedFormat('l j F') }}</span>
                                        <span class="text-[11px] text-[#434655]">{{ $dayItems->count() }} RDV</span>
                                    </a>
                                    @foreach($dayItems as $apt)
                                        <div class="flex items-center justify-between gap-3 px-4 py-3 border-t border-[#eaedff]">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="text-[14px] font-semibold text-[#131b2e] truncate">{{ $apt->patient->name }}</p>
                                                    @include('doctor.partials.status-badge', ['appointment' => $apt])
                                                </div>
                                                <p class="text-[12px] text-[#434655] mt-0.5">
                                                    @if($apt->availability)
                                                        {{ substr($apt->availability->start_time, 0, 5) }} – {{ substr($apt->availability->end_time, 0, 5) }}
                                                    @else
                                                        {{ $apt->appointment_date->format('H:i') }}
                                                    @endif
                                                </p>
                                            </div>
                                            <a href="{{ route('doctor.appointments.show', $apt) }}"
                                               class="h-9 px-3 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[12px] font-semibold inline-flex items-center shrink-0">
                                                Détail
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <p class="text-sm text-[#434655] py-6 text-center">Aucun rendez-vous ce mois-ci.</p>
                            @endforelse
                        </div>
                    @else
                        <div class="space-y-3">
                            @forelse($timeline as $item)
                                @if($item['kind'] === 'appointment')
                                    @php $apt = $item['appointment']; @endphp
                                    <div @class([
                                        'p-4 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4',
                                        'bg-[#dae2fd] shadow-sm' => $item['active'],
                                        'bg-[#f2f3ff]' => ! $item['active'] && $apt->status === 'completed',
                                        'bg-white shadow-sm' => ! $item['active'] && $apt->status !== 'completed',
                                    ])>
                                        <div class="flex items-start gap-4">
                                            <div class="w-20 shrink-0">
                                                <p class="text-[14px] font-bold {{ $item['active'] ? 'text-[#004ac6]' : 'text-[#131b2e]' }}">{{ $item['start']->format('H:i') }}</p>
                                                <p class="text-[11px] {{ $item['active'] ? 'text-[#006242] font-semibold' : 'text-[#434655]' }}">
                                                    {{ $item['active'] ? 'En cours' : $item['minutes'].' min' }}
                                                </p>
                                            </div>
                                            <div class="w-1.5 h-12 rounded-full {{ $item['active'] ? 'bg-[#007d55]' : 'bg-[#c3c6d7]' }} self-center hidden md:block"></div>
                                            <div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-[14px] font-semibold text-[#131b2e]">{{ $apt->patient->name }}</h4>
                                                    @include('doctor.partials.status-badge', ['appointment' => $apt])
                                                </div>
                                                <p class="text-[13px] text-[#434655] mt-0.5">{{ $apt->notes ?: 'Aucune note.' }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('doctor.appointments.show', $apt) }}"
                                           class="h-9 px-3 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[12px] font-semibold inline-flex items-center gap-1 self-end md:self-center">
                                            Détail
                                        </a>
                                    </div>
                                @else
                                    <div class="p-4 rounded-xl bg-[#f2f3ff] flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div class="flex items-start gap-4">
                                            <div class="w-20 shrink-0">
                                                <p class="text-[14px] font-bold text-[#434655]">{{ $item['start']->format('H:i') }}</p>
                                                <p class="text-[11px] text-[#434655]">{{ $item['minutes'] }} min</p>
                                            </div>
                                            <div class="w-1.5 h-12 rounded-full bg-[#e2e7ff] self-center hidden md:block"></div>
                                            <div>
                                                <h4 class="text-[14px] font-semibold text-[#434655] italic">Créneau libre</h4>
                                                <p class="text-[13px] text-[#434655] mt-0.5">Disponible à la réservation.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <p class="text-sm text-[#434655] py-6 text-center">Aucun rendez-vous ni créneau pour cette date.</p>
                            @endforelse
                        </div>
                    @endif
                </div>

                @if($freeSlots === 0)
                    <div class="bg-white border border-[#e2e7ff] rounded-xl px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1">
                            <p class="font-semibold text-[#131b2e]">Aucun créneau libre</p>
                            <p class="text-sm text-[#434655]">Ajoutez des disponibilités pour recevoir de nouvelles demandes.</p>
                        </div>
                        <a href="{{ route('doctor.availabilities.create') }}"
                           class="px-4 py-2 bg-[#004ac6] text-white text-sm font-semibold rounded-lg text-center">
                            Créer une disponibilité
                        </a>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 flex items-center justify-between">
                        <h2 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6]" aria-hidden="true">upcoming</span>
                            Prochains rendez-vous
                        </h2>
                        <a href="{{ route('doctor.appointments.index') }}" class="text-xs font-semibold text-[#004ac6] hover:underline">Voir tous</a>
                    </div>
                    <div class="divide-y divide-[#f2f3ff]">
                        @forelse($upcomingAppointments as $apt)
                            <div class="flex items-center gap-4 px-4 sm:px-6 py-4">
                                <div class="w-14 h-14 bg-[#f2f3ff] rounded-xl flex flex-col items-center justify-center text-[#004ac6] shrink-0">
                                    <span class="text-[10px] font-bold uppercase">{{ $apt->appointment_date->format('M') }}</span>
                                    <span class="text-xl font-bold leading-tight">{{ $apt->appointment_date->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-semibold text-[#131b2e] text-sm">{{ $apt->patient->name }}</p>
                                        @include('doctor.partials.status-badge', ['appointment' => $apt])
                                    </div>
                                    <p class="text-xs text-[#434655] mt-0.5">
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
                                       class="px-3 py-2 border border-[#c3c6d7] rounded-lg text-xs font-semibold text-[#434655] hover:border-[#004ac6] hover:text-[#004ac6]"
                                       aria-label="Voir le rendez-vous de {{ $apt->patient->name }}">
                                        Détail
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-5xl text-[#c3c6d7] block mb-3" aria-hidden="true">calendar_today</span>
                                <p class="text-[#131b2e] font-medium">Aucun rendez-vous à venir.</p>
                                <p class="text-sm text-[#434655] mt-1">Ajoutez des créneaux pour recevoir des demandes.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="lg:col-span-4 flex flex-col gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#006a61] text-[22px]" aria-hidden="true">inbox</span>
                            <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Demandes à trier</h3>
                        </div>
                        @if($pendingAppointments > 0)
                            <span class="px-2 py-0.5 rounded-full bg-[#ffdad6] text-[#93000a] text-[11px] font-bold">{{ $pendingAppointments }}</span>
                        @endif
                    </div>
                    <p class="text-[13px] text-[#434655]">Demandes en attente d’acceptation ou de refus.</p>
                    <div class="space-y-4">
                        @forelse($pendingQueue as $apt)
                            <div class="p-4 rounded-xl bg-[#f2f3ff] shadow-sm flex flex-col gap-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-[14px] font-bold text-[#131b2e]">{{ $apt->patient->name }}</p>
                                        <p class="text-[11px] text-[#434655]">{{ $apt->notes ?: 'Aucune note.' }}</p>
                                    </div>
                                    <span class="text-[11px] font-semibold text-[#004ac6] shrink-0">
                                        {{ $apt->appointment_date->format('d/m') }}
                                        @if($apt->availability)
                                            · {{ substr($apt->availability->start_time, 0, 5) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @include('doctor.partials.pending-actions', ['appointment' => $apt])
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-[#434655]">Aucune demande en attente.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6] text-[22px]" aria-hidden="true">tune</span>
                            <h3 class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Disponibilité de la semaine</h3>
                        </div>
                    </div>
                    <p class="text-[13px] text-[#434655]">Jours où au moins un créneau existe dans l’agenda, pour la semaine affichée.</p>
                    <div class="p-2 rounded-lg bg-[#f2f3ff] flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-[#006a61] text-[20px]" aria-hidden="true">schedule</span>
                            <div>
                                <p class="text-[12px] font-bold text-[#131b2e]">{{ $freeSlots }} créneau(x) libre(s) à venir</p>
                                <p class="text-[11px] text-[#434655]">Géré depuis les disponibilités existantes.</p>
                            </div>
                        </div>
                        <a href="{{ route('doctor.availabilities.index') }}" class="text-[#004ac6] text-[12px] font-semibold shrink-0">Modifier</a>
                    </div>
                    <div class="flex items-center justify-between gap-1 pt-1">
                        @foreach([1 => 'L', 2 => 'M', 3 => 'M', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'] as $iso => $label)
                            <div @class([
                                'flex-1 flex flex-col items-center p-2 rounded-lg text-[11px] font-bold',
                                'bg-[#dbe1ff] text-[#00174b]' => $weekDays[$iso] ?? false,
                                'bg-[#eaedff] text-[#737686]' => ! ($weekDays[$iso] ?? false),
                            ])>
                                <span>{{ $label }}</span>
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">{{ ($weekDays[$iso] ?? false) ? 'check' : 'remove' }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('doctor.availabilities.create') }}"
                       class="w-full h-10 rounded-lg bg-[#e2e7ff] hover:bg-[#eaedff] text-[#004ac6] text-[12px] font-semibold inline-flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">more_time</span>
                        Ajouter un créneau
                    </a>
                </div>

                <div class="p-4 rounded-xl bg-gradient-to-br from-[#004ac6] to-[#2563eb] text-white shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]" aria-hidden="true">apartment</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] opacity-80">Cabinet</p>
                            <p class="text-[14px] font-bold truncate">{{ $doctor->address ?: 'Adresse non renseignée' }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endif
</div>
@endsection
