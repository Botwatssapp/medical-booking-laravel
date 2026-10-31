@extends('layouts.admin')
@section('page-title', 'Rendez-vous')
@section('page-subtitle', 'Supervisez les rendez-vous et leur évolution sur la plateforme.')

@php
    $specialties ??= collect();
    $selected ??= null;
    $patientAppointmentCount ??= 0;
    $stats = array_merge([
        'total' => 0,
        'pending' => 0,
        'accepted' => 0,
        'completed' => 0,
        'cancelled' => 0,
    ], $stats ?? []);
    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? 'P', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    };
    $durationMinutes = function ($appointment): ?int {
        if (! $appointment?->availability) {
            return null;
        }
        $start = \Carbon\Carbon::parse($appointment->availability->start_time);
        $end = \Carbon\Carbon::parse($appointment->availability->end_time);

        return max((int) $start->diffInMinutes($end), 0);
    };
    $hasFilters = request()->hasAny(['status', 'search', 'speciality_id', 'doctor_id', 'date']);
    $sortCol = request('sort', 'appointment_date');
    $sortDir = request('direction', 'desc');
@endphp

@section('admin-content')
<div class="sc-stack w-full">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1 text-[12px] text-[#434655]">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6]">Gestion clinique</a>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span class="text-[#004ac6] font-semibold">Rendez-vous</span>
            </div>
            <h1 class="font-display text-[28px] leading-9 font-bold tracking-tight">Rendez-vous</h1>
            <p class="text-[15px] leading-6 text-[#434655]">Supervisez les rendez-vous et leur évolution sur la plateforme.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 self-start md:self-auto">
            <button type="button"
                    class="h-10 px-4 rounded-lg bg-white text-[#131b2e] text-[14px] font-semibold shadow-sm hover:bg-[#eaedff] flex items-center gap-1"
                    @click="showToast('Les filtres de cette page suffisent — aucun module avancé n’est branché.')">
                <span class="material-symbols-outlined text-[18px] text-[#434655]" aria-hidden="true">tune</span>
                <span>Filtres avancés</span>
            </button>
            <button type="button"
                    class="h-10 px-4 rounded-lg bg-[#2563eb] text-white text-[14px] font-semibold shadow-sm hover:bg-[#004ac6] flex items-center gap-1"
                    @click="showToast('Export du registre non configuré — aperçu visuel uniquement.')">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">file_download</span>
                <span>Exporter le registre</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 w-full">
        @php
            $cards = [
                ['label' => 'Total rendez-vous', 'count' => $stats['total'], 'hint' => 'Tous les statuts', 'dot' => 'bg-[#004ac6]', 'val' => ''],
                ['label' => 'En attente', 'count' => $stats['pending'], 'hint' => 'À traiter', 'dot' => 'bg-amber-400', 'val' => 'pending'],
                ['label' => 'Acceptés', 'count' => $stats['accepted'], 'hint' => 'Acceptés par l’équipe', 'dot' => 'bg-[#2563eb]', 'val' => 'accepted'],
                ['label' => 'Terminés', 'count' => $stats['completed'], 'hint' => 'Consultations closes', 'dot' => 'bg-[#006a61]', 'val' => 'completed'],
                ['label' => 'Annulés', 'count' => $stats['cancelled'], 'hint' => 'Annulations enregistrées', 'dot' => 'bg-[#ba1a1a]', 'val' => 'cancelled'],
            ];
        @endphp
        @foreach($cards as $card)
            <a href="{{ route('admin.appointments.index', array_filter(['status' => $card['val'] ?: null, 'search' => request('search')])) }}"
               class="min-w-0 p-5 rounded-xl bg-white shadow-sm flex flex-col min-h-32 {{ request('status', '') === $card['val'] ? 'ring-2 ring-[#004ac6]/25' : 'hover:ring-1 hover:ring-[#e2e7ff]' }}">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] uppercase tracking-wider font-semibold text-[#434655] leading-4">{{ $card['label'] }}</span>
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 mt-0.5 {{ $card['dot'] }}"></span>
                </div>
                <p class="font-display text-[36px] leading-none font-bold tracking-tight mt-3 text-[#131b2e]">{{ $card['count'] }}</p>
                <p class="text-[12px] leading-4 text-[#434655] mt-auto pt-3">{{ $card['hint'] }}</p>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.appointments.index') }}"
          class="p-4 rounded-xl bg-white shadow-sm sc-toolbar">
        <div class="relative flex-1 min-w-0">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#434655] text-[20px]" aria-hidden="true">search</span>
            <label for="appointment-search" class="sr-only">Rechercher un patient ou un médecin</label>
            <input class="w-full h-10 pl-10 pr-4 rounded-lg bg-[#f2f3ff] text-[13px] focus:outline-none focus:bg-white"
                   type="search" id="appointment-search" name="search" value="{{ request('search') }}"
                   placeholder="Nom ou e-mail du patient ou du médecin">
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:flex lg:items-center gap-2">
            <x-ui.select name="status" id="appointment-status" icon="filter_list" label="Filtrer par statut">
                <option value="">Tous les statuts</option>
                <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                <option value="accepted" @selected(request('status') === 'accepted')>Accepté</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Refusé</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Annulé</option>
                <option value="completed" @selected(request('status') === 'completed')>Terminé</option>
                <option value="missed" @selected(request('status') === 'missed')>Non réalisé</option>
            </x-ui.select>
            <x-ui.select name="speciality_id" icon="medical_services" label="Filtrer par spécialité">
                <option value="">Toutes spécialités</option>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected((int) request('speciality_id') === $specialty->id)>{{ $specialty->name }}</option>
                @endforeach
            </x-ui.select>
            <label class="h-10 px-3 rounded-lg bg-[#f2f3ff] text-[12px] font-semibold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-[#434655]" aria-hidden="true">event</span>
                <input type="date" name="date" value="{{ request('date') }}" class="bg-transparent focus:outline-none">
            </label>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.select name="sort" label="Trier">
                <option value="appointment_date" @selected($sortCol === 'appointment_date')>Date</option>
                <option value="status" @selected($sortCol === 'status')>Statut</option>
            </x-ui.select>
            <x-ui.select name="direction" label="Sens du tri">
                <option value="desc" @selected($sortDir === 'desc')>Récent</option>
                <option value="asc" @selected($sortDir === 'asc')>Ancien</option>
            </x-ui.select>
            <button type="submit" class="h-10 px-4 rounded-lg bg-[#004ac6] text-white text-[12px] font-semibold">Filtrer</button>
            @if($hasFilters)
                <a href="{{ route('admin.appointments.index') }}" class="h-10 px-3 rounded-lg bg-[#eaedff] text-[12px] font-semibold flex items-center">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        <div class="xl:col-span-8 flex flex-col rounded-xl bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between bg-[#f2f3ff]/70">
                <div class="flex items-center gap-2">
                    <span class="font-display text-[16px] leading-6 font-semibold">Registre des séances</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[#434655] text-[11px] font-semibold">
                        {{ $appointments->count() }} affiché{{ $appointments->count() > 1 ? 's' : '' }}
                        sur {{ $appointments->total() }}
                    </span>
                </div>
                <a href="{{ request()->fullUrl() }}" class="p-1.5 rounded-md hover:bg-[#eaedff] text-[#434655]" title="Actualiser">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">refresh</span>
                    <span class="sr-only">Actualiser</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] min-w-[760px]">
                    <thead>
                        <tr class="text-[#434655] text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-4 font-semibold" scope="col">Patient</th>
                            <th class="py-3 px-4 font-semibold" scope="col">Médecin</th>
                            <th class="py-3 px-4 font-semibold" scope="col">Spécialité</th>
                            <th class="py-3 px-4 font-semibold" scope="col">Date &amp; heure</th>
                            <th class="py-3 px-4 font-semibold" scope="col">Statut</th>
                            <th class="py-3 px-4 font-semibold text-right" scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            @php
                                $canCancel = ! in_array($appointment->status, ['cancelled', 'completed', 'missed'], true);
                                $isSelected = $selected && (int) $selected->id === (int) $appointment->id;
                                $minutes = $durationMinutes($appointment);
                            @endphp
                            <tr class="{{ $isSelected ? 'bg-[#f2f3ff]' : 'hover:bg-[#f2f3ff]/70' }}">
                                <td class="py-3.5 px-4">
                                    <a href="{{ request()->fullUrlWithQuery(['selected' => $appointment->id]) }}" class="flex items-center gap-2 min-w-0">
                                        <div class="w-9 h-9 rounded-full bg-[#004ac6] text-white flex items-center justify-center text-[12px] font-bold shrink-0">
                                            {{ $initials($appointment->patient->name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[14px] font-semibold truncate">{{ $appointment->patient->name }}</p>
                                            <p class="text-[11px] text-[#434655] font-mono">#{{ $appointment->id }}</p>
                                        </div>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-7 h-7 rounded-full bg-[#eaedff] text-[#004ac6] flex items-center justify-center text-[11px] font-bold">
                                            {{ $initials($appointment->doctor->user->name) }}
                                        </div>
                                        <span class="text-[12px] font-semibold">Dr {{ $appointment->doctor->user->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full bg-[#eaedff] text-[#004ac6] text-[11px] font-semibold">
                                        {{ $appointment->doctor->speciality->name }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="text-[12px] font-semibold">{{ $appointment->appointment_date->format('d/m/Y') }}</p>
                                    <p class="text-[11px] text-[#434655]">
                                        @if($appointment->availability)
                                            {{ \Carbon\Carbon::parse($appointment->availability->start_time)->format('H:i') }}
                                            @if($minutes)
                                                ({{ $minutes }} min)
                                            @endif
                                        @else
                                            {{ $appointment->appointment_date->format('H:i') }}
                                        @endif
                                    </p>
                                </td>
                                <td class="py-3.5 px-4">
                                    @include('admin.partials.status-badge', ['appointment' => $appointment])
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1 flex-wrap">
                                        <a href="{{ route('admin.appointments.show', $appointment) }}"
                                           class="p-1.5 rounded-lg hover:bg-[#e2e7ff] text-[#004ac6]" title="Fiche complète">
                                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">visibility</span>
                                            <span class="sr-only">Détails</span>
                                        </a>
                                        @if($appointment->status === 'pending')
                                            <form method="POST" action="{{ route('admin.appointments.updateStatus', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" class="px-2 py-1 rounded-md bg-[#2563eb] text-white text-[11px] font-semibold">Accepter</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.appointments.updateStatus', $appointment) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="p-1 rounded-md hover:bg-[#ffdad6] text-[#ba1a1a]" title="Refuser">
                                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                                                    <span class="sr-only">Refuser</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if($canCancel)
                                            <form method="POST" action="{{ route('admin.appointments.cancel', $appointment) }}" class="inline"
                                                  onsubmit="return confirm('Annuler ce rendez-vous ?')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-[#ffdad6] text-[#434655] hover:text-[#ba1a1a]" title="Annuler">
                                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">cancel</span>
                                                    <span class="sr-only">Annuler</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-[#434655]">
                                    <span class="material-symbols-outlined text-4xl text-[#c3c6d7] block mb-2" aria-hidden="true">calendar_month</span>
                                    @if($hasFilters)
                                        <p class="font-semibold text-[#131b2e]">Aucun résultat pour cette recherche</p>
                                        <a href="{{ route('admin.appointments.index') }}" class="inline-block mt-3 text-sm font-semibold text-[#004ac6] hover:underline">Réinitialiser les filtres</a>
                                    @else
                                        <p class="font-semibold text-[#131b2e]">Aucun rendez-vous</p>
                                        <p class="text-sm mt-1">Les demandes des patients apparaîtront ici.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($appointments->total() > 0)
                <div class="px-4 sm:px-6 py-3 sc-pager">
                    <p class="sc-pager__label">
                        Affichage de <span class="font-semibold text-[#131b2e]">{{ $appointments->firstItem() }}</span>
                        à <span class="font-semibold text-[#131b2e]">{{ $appointments->lastItem() }}</span>
                        sur <span class="font-semibold text-[#131b2e]">{{ $appointments->total() }}</span> rendez-vous
                    </p>
                    <div class="sc-pager__pages">{{ $appointments->links() }}</div>
                </div>
            @endif
        </div>

        <aside class="xl:col-span-4 rounded-xl bg-white shadow-sm flex flex-col overflow-hidden">
            @if($selected)
                @php
                    $selectedMinutes = $durationMinutes($selected);
                    $selectedTime = $selected->availability
                        ? \Carbon\Carbon::parse($selected->availability->start_time)->format('H:i')
                        : $selected->appointment_date->format('H:i');
                @endphp
                <div class="p-6 bg-gradient-to-r from-[#f2f3ff] to-[#eaedff] flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        @include('admin.partials.status-badge', ['appointment' => $selected])
                        <div class="flex items-center gap-1">
                            <span class="font-mono text-[11px] text-[#434655]">#{{ $selected->id }}</span>
                            <a href="{{ route('admin.appointments.index', request()->except('selected')) }}"
                               class="p-1 rounded-md text-[#434655] hover:bg-white" title="Fermer le panneau">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h2 class="font-display text-[20px] leading-7 font-semibold">Détails de la consultation</h2>
                        <p class="text-[13px] text-[#434655]">Créé le {{ $selected->created_at->format('d/m/Y') }} via le compte patient</p>
                    </div>
                </div>
                <div class="p-6 flex flex-col gap-6">
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-[#434655] font-semibold mb-2">Patient assigné</p>
                        <div class="p-4 rounded-lg bg-[#f2f3ff] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-full bg-[#004ac6] text-white font-bold flex items-center justify-center shrink-0">
                                    {{ $initials($selected->patient->name) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[14px] font-semibold truncate">{{ $selected->patient->name }}</p>
                                    <p class="text-[11px] text-[#434655] truncate">{{ $selected->patient->email }}</p>
                                    @if($selected->patient->phone)
                                        <p class="text-[11px] text-[#006a61] font-medium">{{ $selected->patient->phone }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[11px] font-semibold">{{ $patientAppointmentCount }} RDV</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-[#434655] font-semibold mb-2">Praticien référent</p>
                        <div class="p-4 rounded-lg bg-[#f2f3ff]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#004ac6] text-white font-bold flex items-center justify-center">
                                    {{ $initials($selected->doctor->user->name) }}
                                </div>
                                <div>
                                    <p class="text-[14px] font-semibold">Dr {{ $selected->doctor->user->name }}</p>
                                    <p class="text-[11px] text-[#004ac6] font-medium">{{ $selected->doctor->speciality->name }}</p>
                                </div>
                            </div>
                            @if($selected->doctor->address)
                                <p class="mt-2 flex items-start gap-1.5 text-[13px] text-[#434655]">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">location_on</span>
                                    <span>{{ $selected->doctor->address }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wider text-[#434655] font-semibold mb-2">Informations du créneau</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-3 rounded-lg bg-[#f2f3ff]">
                                <p class="text-[11px] text-[#434655]">Date de séance</p>
                                <p class="text-[14px] font-semibold mt-0.5">{{ $selected->appointment_date->format('d/m/Y') }}</p>
                            </div>
                            <div class="p-3 rounded-lg bg-[#f2f3ff]">
                                <p class="text-[11px] text-[#434655]">Horaire</p>
                                <p class="text-[14px] font-semibold mt-0.5">
                                    {{ $selectedTime }}
                                    @if($selectedMinutes)
                                        ({{ $selectedMinutes }} min)
                                    @endif
                                </p>
                            </div>
                            <div class="p-3 rounded-lg bg-[#f2f3ff] col-span-2">
                                <p class="text-[11px] text-[#434655]">Modalité</p>
                                <p class="text-[14px] font-semibold mt-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-[#006a61]" aria-hidden="true">domain</span>
                                    Présentiel
                                </p>
                            </div>
                        </div>
                    </div>
                    @if($selected->notes)
                        <div class="p-4 rounded-lg bg-[#e2e7ff]/60">
                            <p class="text-[11px] font-semibold uppercase text-[#434655] flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">notes</span>
                                Notes du patient à la réservation
                            </p>
                            <p class="text-[13px] mt-1 whitespace-pre-line">{{ $selected->notes }}</p>
                        </div>
                    @endif
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.appointments.show', $selected) }}"
                               class="flex-1 h-10 px-4 rounded-lg bg-[#2563eb] text-white text-[14px] font-semibold flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">edit_calendar</span>
                                Modifier le statut
                            </a>
                            <button type="button" class="h-10 px-3 rounded-lg bg-[#f2f3ff] text-[#131b2e]"
                                    @click="showToast('Impression non configurée — ouvrez la fiche pour les détails.')"
                                    title="Imprimer">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">print</span>
                            </button>
                        </div>
                        <a href="{{ route('admin.appointments.index', request()->except('selected')) }}"
                           class="w-full h-9 rounded-lg text-[#434655] text-[12px] font-semibold flex items-center justify-center">
                            Fermer le panneau
                        </a>
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-[#434655]">
                    <span class="material-symbols-outlined text-4xl text-[#c3c6d7] block mb-2" aria-hidden="true">info</span>
                    <p class="font-semibold text-[#131b2e]">Aucun rendez-vous sélectionné</p>
                    <p class="text-sm mt-1">Choisissez une ligne pour afficher le détail réel.</p>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
