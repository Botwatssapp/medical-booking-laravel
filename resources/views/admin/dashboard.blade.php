@extends('layouts.admin')
@section('title', 'Opérations cliniques')
@section('page-title', 'Opérations cliniques')
@section('page-subtitle', 'Activité réelle du réseau SantéConnect')

@section('page-actions')
    <span class="hidden sm:inline-flex items-center h-11 px-3 rounded-xl bg-[#f2f3ff] text-[12px] font-semibold text-[#434655] whitespace-nowrap">
        <span class="material-symbols-outlined text-[18px] mr-1 text-[#004ac6]" aria-hidden="true">schedule</span>
        {{ now()->format('H:i') }}
    </span>
    <a href="{{ route('admin.appointments.index') }}" class="sc-action-btn sc-action-btn--ghost">
        <span class="material-symbols-outlined" aria-hidden="true">download</span>
        Exporter
    </a>
    <button type="button" class="sc-action-btn sc-action-btn--primary" @click="showToast('Diffusion non configurée — aperçu visuel uniquement.')">
        <span class="material-symbols-outlined" aria-hidden="true">add_moderator</span>
        Diffuser
    </button>
@endsection

@section('admin-content')
@php
    $specialtyTotal = max((int) $specialtyDemand->sum('total'), 1);
    $palette = ['#004ac6', '#2563eb', '#006a61', '#6bd8cb', '#737686', '#64748b'];
    $statusChips = [
        ['label' => 'Acceptés', 'count' => $acceptedAppointments],
        ['label' => 'Terminés', 'count' => $completedAppointments],
        ['label' => 'Annulés', 'count' => $cancelledAppointments],
        ['label' => 'Refusés', 'count' => $rejectedAppointments],
        ['label' => 'Non réalisés', 'count' => $missedAppointments],
    ];
@endphp

<div class="sc-stack w-full">
    <div class="min-w-0">
        <div class="flex items-center gap-2 text-[12px] font-semibold text-[#434655]">
            <span>Santé du réseau</span>
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#007d55]"></span>
            <span class="text-[#006242]">Synchronisation active</span>
        </div>
        <h1 class="font-display mt-1 text-[28px] leading-9 font-bold tracking-tight text-[#131b2e]">Opérations cliniques</h1>
        <p class="mt-1 text-[13px] leading-5 text-[#434655]">Activité réelle du réseau SantéConnect — aucune statistique marketing n’est inventée ici.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 w-full">
        <article class="sc-stat-card">
            <p class="sc-stat-card__label">Rendez-vous</p>
            <div class="sc-stat-card__row">
                <p class="sc-stat-card__value">{{ $totalAppointments }}</p>
                <div class="sc-stat-card__icon bg-[#eaedff] text-[#004ac6]">
                    <span class="material-symbols-outlined" aria-hidden="true">event_available</span>
                </div>
            </div>
            <p class="sc-stat-card__hint">{{ $todayAppointments }} aujourd’hui</p>
        </article>
        <article class="sc-stat-card">
            <p class="sc-stat-card__label">Praticiens actifs</p>
            <div class="sc-stat-card__row">
                <p class="sc-stat-card__value">{{ $totalDoctors }}</p>
                <div class="sc-stat-card__icon bg-[#eaedff] text-[#004ac6]">
                    <span class="material-symbols-outlined" aria-hidden="true">stethoscope</span>
                </div>
            </div>
            <p class="sc-stat-card__hint">{{ $pendingDoctorCount }} en attente</p>
        </article>
        <article class="sc-stat-card">
            <p class="sc-stat-card__label">Patients inscrits</p>
            <div class="sc-stat-card__row">
                <p class="sc-stat-card__value">{{ $totalPatients }}</p>
                <div class="sc-stat-card__icon bg-[#eaedff] text-[#004ac6]">
                    <span class="material-symbols-outlined" aria-hidden="true">groups</span>
                </div>
            </div>
            <p class="sc-stat-card__hint">{{ $totalUsers }} utilisateurs · +{{ $patientsThisWeek }} cette semaine</p>
        </article>
        <article class="sc-stat-card">
            <p class="sc-stat-card__label">Occupation</p>
            <div class="sc-stat-card__row">
                <p class="sc-stat-card__value">{{ $fillRate === null ? '—' : $fillRate.' %' }}</p>
                <div class="sc-stat-card__icon bg-[#89f5e7]/30 text-[#006a61]">
                    <span class="material-symbols-outlined" aria-hidden="true">speed</span>
                </div>
            </div>
            @if($fillRate !== null)
                <div class="w-full bg-[#e2e7ff] h-1.5 rounded-full mt-3 overflow-hidden">
                    <div class="bg-[#2563eb] h-full rounded-full" style="width: {{ min($fillRate, 100) }}%"></div>
                </div>
            @endif
            <p class="sc-stat-card__hint">{{ $freeSlots }} créneaux libres</p>
        </article>
    </div>

    <section class="bg-white rounded-xl shadow-sm px-5 py-4 flex flex-col xl:flex-row xl:items-center gap-4 w-full min-w-0">
        <div class="flex items-center gap-3 min-w-0 shrink-0">
            <div class="sc-stat-card__icon bg-[#89f5e7]/30 text-[#006a61]">
                <span class="material-symbols-outlined" aria-hidden="true">hub</span>
            </div>
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-[#434655] leading-5">Modalité</p>
                <p class="font-display text-[20px] leading-7 font-bold tracking-tight text-[#131b2e]">Présentiel</p>
            </div>
            <p class="hidden md:block text-[12px] leading-4 text-[#434655] max-w-[14rem]">La visio n’est pas distinguée en base</p>
        </div>
        <div class="hidden xl:block w-px self-stretch bg-[#e2e7ff]"></div>
        <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
            @foreach($statusChips as $chip)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#f2f3ff] text-[12px] font-semibold text-[#434655]">
                    <span class="text-[#131b2e]">{{ $chip['count'] }}</span>
                    {{ $chip['label'] }}
                </span>
            @endforeach
            @if($doctorAccounts > $totalDoctors)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f2f3ff] text-[12px] font-semibold text-[#434655]">
                    {{ $doctorAccounts }} comptes médecin
                </span>
            @endif
            @if($appointmentDelta !== null)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#f2f3ff] text-[12px] font-semibold {{ $appointmentDelta >= 0 ? 'text-[#006242]' : 'text-[#ba1a1a]' }}">
                    {{ $appointmentDelta >= 0 ? '+' : '' }}{{ $appointmentDelta }} % vs mois dernier
                </span>
            @endif
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
        <section class="lg:col-span-8 bg-white p-5 sm:p-6 rounded-xl shadow-sm min-w-0" x-data="{ period: 'daily' }">
            @php
                $chartMeta = [
                    'daily' => [
                        'title' => 'Trajectoire hebdomadaire',
                        'caption' => 'Volume réel des 7 derniers jours',
                        'aria' => 'Volume de rendez-vous sur 7 jours',
                    ],
                    'weekly' => [
                        'title' => 'Trajectoire par semaine',
                        'caption' => 'Volume réel des 7 dernières semaines',
                        'aria' => 'Volume de rendez-vous sur 7 semaines',
                    ],
                    'monthly' => [
                        'title' => 'Trajectoire mensuelle',
                        'caption' => 'Volume réel des 7 derniers mois',
                        'aria' => 'Volume de rendez-vous sur 7 mois',
                    ],
                ];
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]"
                            x-text="period === 'weekly' ? 'Trajectoire par semaine' : (period === 'monthly' ? 'Trajectoire mensuelle' : 'Trajectoire hebdomadaire')">Trajectoire hebdomadaire</h2>
                        <span class="bg-[#e2e7ff] text-[#004ac6] text-[11px] font-semibold px-2 py-0.5 rounded-full">Cycle actif</span>
                    </div>
                    <p class="text-[13px] text-[#434655] mt-0.5"
                       x-text="period === 'weekly' ? 'Volume réel des 7 dernières semaines' : (period === 'monthly' ? 'Volume réel des 7 derniers mois' : 'Volume réel des 7 derniers jours')">Volume réel des 7 derniers jours</p>
                </div>
                <div class="flex items-center gap-1 bg-[#f2f3ff] p-1 rounded-lg shrink-0" role="tablist" aria-label="Période du graphique">
                    <button type="button" role="tab" class="px-2.5 py-1 rounded-md text-[11px] font-semibold" :class="period === 'daily' ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655]'" :aria-selected="(period === 'daily').toString()" @click="period = 'daily'">Jour</button>
                    <button type="button" role="tab" class="px-2.5 py-1 rounded-md text-[11px] font-semibold" :class="period === 'weekly' ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655]'" :aria-selected="(period === 'weekly').toString()" @click="period = 'weekly'">Semaine</button>
                    <button type="button" role="tab" class="px-2.5 py-1 rounded-md text-[11px] font-semibold" :class="period === 'monthly' ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655]'" :aria-selected="(period === 'monthly').toString()" @click="period = 'monthly'">Mois</button>
                </div>
            </div>
            @foreach(['daily', 'weekly', 'monthly'] as $periodKey)
                @php
                    $rows = collect($chartSeries[$periodKey] ?? []);
                    $periodMax = max((int) $rows->max('count'), 1);
                    $periodPoints = $rows->values()->map(function ($row, $index) use ($rows, $periodMax) {
                        $count = $rows->count();
                        $x = $count > 1 ? ($index / ($count - 1)) * 660 + 20 : 350;
                        $y = 150 - (($row['count'] / $periodMax) * 120);

                        return ['x' => $x, 'y' => $y, 'label' => $row['label'], 'count' => $row['count']];
                    });
                    $periodLine = $periodPoints->map(fn ($p) => $p['x'].','.$p['y'])->implode(' ');
                    $periodArea = $periodLine.' 680,180 20,180';
                    $periodPeak = $rows->sortByDesc('count')->first();
                @endphp
                <div @if($periodKey !== 'daily') x-cloak @endif x-show="period === '{{ $periodKey }}'">
                    <div class="relative w-full">
                        <svg viewBox="0 0 700 180" class="w-full h-48 sm:h-56" preserveAspectRatio="none" role="img" aria-label="{{ $chartMeta[$periodKey]['aria'] }}">
                            <defs>
                                <linearGradient id="opsArea-{{ $periodKey }}" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#004ac6" stop-opacity="0.22"></stop>
                                    <stop offset="100%" stop-color="#004ac6" stop-opacity="0"></stop>
                                </linearGradient>
                            </defs>
                            <line x1="0" x2="700" y1="30" y2="30" stroke="#eaedff" stroke-dasharray="4 4"></line>
                            <line x1="0" x2="700" y1="80" y2="80" stroke="#eaedff" stroke-dasharray="4 4"></line>
                            <line x1="0" x2="700" y1="130" y2="130" stroke="#eaedff" stroke-dasharray="4 4"></line>
                            <polygon points="{{ $periodArea }}" fill="url(#opsArea-{{ $periodKey }})"></polygon>
                            <polyline fill="none" stroke="#004ac6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="{{ $periodLine }}"></polyline>
                            @foreach($periodPoints as $point)
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="#faf8ff" stroke="#004ac6" stroke-width="3"></circle>
                            @endforeach
                        </svg>
                        <div class="grid text-[11px] text-[#434655] mt-1" style="grid-template-columns: repeat({{ max($rows->count(), 1) }}, minmax(0, 1fr));">
                            @foreach($rows as $row)
                                <span class="text-center min-w-0">
                                    <span class="block truncate font-medium">{{ $row['label'] }}</span>
                                    <span class="font-semibold text-[#131b2e]">{{ $row['count'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @if($periodPeak)
                        <p class="text-[11px] text-[#434655] mt-4">
                            Pic : <strong class="text-[#131b2e]">{{ $periodPeak['count'] }} rendez-vous ({{ $periodPeak['label'] }})</strong>
                        </p>
                    @endif
                </div>
            @endforeach
        </section>

        <section class="lg:col-span-4 bg-white p-5 sm:p-6 rounded-xl shadow-sm min-w-0">
            <div class="flex items-center justify-between gap-2 mb-1">
                <h2 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Demande par spécialité</h2>
                <span class="material-symbols-outlined text-[#434655] text-[20px] shrink-0" aria-hidden="true">donut_large</span>
            </div>
            <p class="text-[13px] text-[#434655] mb-5">Répartition réelle des rendez-vous</p>
            <div class="space-y-4">
                @forelse($specialtyDemand as $index => $row)
                    @php $share = (int) round(($row->total / $specialtyTotal) * 100); @endphp
                    <div>
                        <div class="flex items-center justify-between gap-2 text-[12px] mb-1">
                            <span class="inline-flex items-center gap-1.5 font-semibold min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background: {{ $palette[$index % count($palette)] }}" aria-hidden="true"></span>
                                <span class="truncate">{{ $row->name }}</span>
                            </span>
                            <span class="font-bold text-[#131b2e] shrink-0">{{ $share }}&nbsp;%</span>
                        </div>
                        <div class="h-2 bg-[#e2e7ff] rounded-full overflow-hidden">
                            <div class="h-2 rounded-full" style="width: {{ $share }}%; background: {{ $palette[$index % count($palette)] }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#434655]">Aucune spécialité n’a encore de rendez-vous.</p>
                @endforelse
            </div>
            @if($specialtyDemand->isNotEmpty())
                <div class="mt-5 p-3 bg-[#f2f3ff] rounded-lg flex items-start gap-2">
                    <span class="material-symbols-outlined text-[#006a61] text-[22px] shrink-0" aria-hidden="true">insights</span>
                    <p class="text-[13px] text-[#434655] min-w-0">
                        Spécialité la plus demandée :
                        <strong class="text-[#131b2e]">{{ $specialtyDemand->first()->name }}</strong>
                        ({{ $specialtyDemand->first()->total }} rendez-vous).
                    </p>
                </div>
            @endif
        </section>
    </div>

    @php $hasPendingQueue = $pendingDoctors->isNotEmpty(); @endphp
    <div class="grid grid-cols-1 {{ $hasPendingQueue ? 'lg:grid-cols-12' : '' }} gap-4 items-stretch">
        <section class="{{ $hasPendingQueue ? 'lg:col-span-8' : 'w-full' }} bg-white rounded-xl shadow-sm overflow-hidden min-w-0" x-data="{ status: '', query: '' }">
            <div class="p-5 sm:p-6 flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Registre des rendez-vous</h2>
                        <p class="text-[13px] text-[#434655]">Dernières demandes enregistrées</p>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] font-semibold text-[#006242] shrink-0">
                        <span class="flex h-2 w-2 rounded-full bg-[#007d55]"></span>
                        Données réelles
                    </div>
                </div>
                @unless($hasPendingQueue)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl bg-[#f2f3ff] px-4 py-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-[#004ac6] text-[20px] shrink-0" aria-hidden="true">verified_user</span>
                            <p class="text-[13px] text-[#434655] min-w-0">
                                <span class="font-semibold text-[#131b2e]">File de vérification</span>
                                — Aucun médecin en attente de validation.
                            </p>
                        </div>
                        <a href="{{ route('admin.doctors.create') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-[#004ac6] shrink-0">
                            Voir toutes les validations
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_forward</span>
                        </a>
                    </div>
                @endunless
                <div class="grid grid-cols-1 sm:grid-cols-2 {{ $hasPendingQueue ? 'lg:grid-cols-12' : 'xl:grid-cols-12' }} gap-3">
                    <div class="{{ $hasPendingQueue ? 'lg:col-span-5' : 'xl:col-span-5' }} relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#434655] text-[18px] pointer-events-none" aria-hidden="true">search</span>
                        <label class="sr-only" for="ops-search">Rechercher un patient ou un médecin</label>
                        <input id="ops-search" type="search" x-model="query" placeholder="Patient ou médecin"
                               class="w-full h-11 pl-10 pr-3 rounded-xl bg-[#f2f3ff] text-sm focus:outline-none focus:ring-2 focus:ring-[#004ac6]/25 focus:bg-white">
                    </div>
                    <div class="{{ $hasPendingQueue ? 'lg:col-span-4' : 'xl:col-span-4' }} min-w-0">
                        <label class="sr-only" for="ops-status">Filtrer par statut</label>
                        <x-ui.select id="ops-status" label="Filtrer par statut" x-model="status">
                            <option value="">Tous les statuts</option>
                            <option value="pending">En attente</option>
                            <option value="accepted">Accepté</option>
                            <option value="rejected">Refusé</option>
                            <option value="cancelled">Annulé</option>
                            <option value="completed">Terminé</option>
                            <option value="missed">Non réalisé</option>
                        </x-ui.select>
                    </div>
                    <div class="{{ $hasPendingQueue ? 'lg:col-span-3' : 'xl:col-span-3' }} h-11 px-3 rounded-xl bg-[#f2f3ff] text-sm text-[#434655] font-semibold flex items-center whitespace-nowrap">
                        {{ now()->translatedFormat('d M Y') }}
                    </div>
                </div>
            </div>
            <div class="w-full">
                <table class="w-full table-fixed text-sm">
                    <thead class="bg-[#f2f3ff] text-[11px] uppercase tracking-wider text-[#434655]">
                        <tr class="text-left">
                            <th class="pl-4 lg:pl-5 pr-2 py-3 font-semibold w-16 hidden sm:table-cell">ID</th>
                            <th class="px-2 py-3 font-semibold">Patient</th>
                            <th class="px-2 py-3 font-semibold w-[18%] hidden md:table-cell">Praticien</th>
                            <th class="px-2 py-3 font-semibold w-28 hidden sm:table-cell">Horaire</th>
                            <th class="px-2 py-3 font-semibold w-24 hidden sm:table-cell">Statut</th>
                            <th class="pr-4 lg:pr-5 pl-2 py-3 font-semibold w-24 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAppointments as $apt)
                            @php
                                $aptTime = $apt->availability
                                    ? substr($apt->availability->start_time, 0, 5)
                                    : $apt->appointment_date->format('H:i');
                            @endphp
                            <tr class="hover:bg-[#f2f3ff]/60"
                                x-show="(status === '' || status === '{{ $apt->status }}') && ('{{ strtolower($apt->patient->name.' '.$apt->doctor->user->name) }}').includes(query.toLowerCase())">
                                <td class="pl-4 lg:pl-5 pr-2 py-3.5 font-semibold text-[#004ac6] hidden sm:table-cell">#{{ $apt->id }}</td>
                                <td class="px-2 py-3.5 min-w-0">
                                    <span class="font-semibold text-[#131b2e] block truncate" title="{{ $apt->patient->name }}">{{ $apt->patient->name }}</span>
                                    @if($apt->patient->email)
                                        <span class="text-[#434655] text-[11px] truncate block" title="{{ $apt->patient->email }}">{{ $apt->patient->email }}</span>
                                    @endif
                                    <span class="sm:hidden block text-[11px] text-[#434655] mt-0.5">{{ $apt->appointment_date->format('d/m/Y') }} · {{ $aptTime }}</span>
                                </td>
                                <td class="px-2 py-3.5 hidden md:table-cell min-w-0">
                                    <span class="block truncate">Dr {{ $apt->doctor->user->name }}</span>
                                    <span class="block text-[11px] text-[#434655] truncate">{{ $apt->doctor->speciality->name }}</span>
                                </td>
                                <td class="px-2 py-3.5 text-[#434655] hidden sm:table-cell">
                                    <span class="block whitespace-nowrap">{{ $apt->appointment_date->format('d/m/Y') }}</span>
                                    <span class="block text-[11px] whitespace-nowrap">{{ $aptTime }}</span>
                                </td>
                                <td class="px-2 py-3.5 hidden sm:table-cell">
                                    @include('admin.partials.status-badge', ['appointment' => $apt])
                                </td>
                                <td class="pr-4 lg:pr-5 pl-2 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.appointments.show', $apt) }}" class="inline-flex h-9 items-center px-2 rounded-lg text-xs font-semibold text-[#2563eb] hover:bg-[#eaedff]">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-[#434655]">
                                    <p class="font-medium text-[#131b2e]">Aucun rendez-vous récent</p>
                                    <p class="mt-1">Les nouvelles demandes apparaîtront ici.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-[#f2f3ff]/40 flex items-center justify-between gap-3 text-[11px] text-[#434655]">
                <span>{{ $recentAppointments->count() }} ligne(s) affichée(s)</span>
                <a href="{{ route('admin.appointments.index') }}" class="font-semibold text-[#004ac6] whitespace-nowrap">Voir tous</a>
            </div>
        </section>

        @if($hasPendingQueue)
            <section class="lg:col-span-4 bg-white p-5 sm:p-6 rounded-xl shadow-sm min-w-0 h-full flex flex-col">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="material-symbols-outlined text-[#004ac6] text-[22px] shrink-0" aria-hidden="true">verified_user</span>
                        <h2 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">File de vérification</h2>
                    </div>
                    <span class="bg-[#ffdad6] text-[#93000a] px-2 py-0.5 rounded-full text-[11px] font-bold shrink-0">{{ $pendingDoctorCount }} urgent</span>
                </div>
                <p class="text-[13px] text-[#434655] mb-4">Médecins en attente de profil</p>
                <div class="space-y-3 flex-1">
                    @foreach($pendingDoctors as $pending)
                        <div class="rounded-xl bg-[#f2f3ff] p-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-lg bg-[#e2e7ff] text-[#004ac6] font-bold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($pending->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold truncate">{{ $pending->name }}</p>
                                    <p class="text-[11px] text-[#434655] truncate">{{ $pending->email }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <a href="{{ route('admin.doctors.create', ['user_id' => $pending->id]) }}"
                                   class="flex-1 text-center h-10 inline-flex items-center justify-center bg-[#004ac6] text-white px-2 rounded-lg text-[12px] font-semibold">
                                    Valider
                                </a>
                                <button type="button"
                                        class="flex-1 h-10 bg-[#dae2fd] hover:bg-[#e2e7ff] text-[#131b2e] px-2 rounded-lg text-[12px] font-medium"
                                        @click="showToast('Demande de document non configurée — aperçu visuel.')">
                                    Document
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('admin.doctors.create') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-[#004ac6]">
                    Voir toutes les validations
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_forward</span>
                </a>
            </section>
        @endif
    </div>
</div>
@endsection
