@php
    $slotMinutes = static function ($slot): int {
        $start = \Carbon\Carbon::parse((string) $slot->start_time);
        $end = \Carbon\Carbon::parse((string) $slot->end_time);

        return max(0, $start->diffInMinutes($end));
    };
    $weekMinutes = $weekSlots->sum($slotMinutes);
    $weekHours = intdiv($weekMinutes, 60);
    $weekRest = $weekMinutes % 60;
    $weekVolume = $weekMinutes < 1
        ? '0'
        : ($weekMinutes < 60
            ? (string) $weekMinutes
            : ($weekRest === 0 ? (string) $weekHours : $weekHours.'h'.str_pad((string) $weekRest, 2, '0', STR_PAD_LEFT)));
    $weekVolumeUnit = $weekMinutes < 1 ? 'min' : ($weekMinutes < 60 ? 'min' : ($weekRest === 0 ? 'h / sem.' : ''));
    $weekDayCount = $weekSlots->map(fn ($slot) => $slot->date->toDateString())->unique()->count();
    $occupancy = $stats['total'] > 0 ? (int) round(($stats['booked'] * 100) / $stats['total']) : 0;
    $freeRate = $stats['total'] > 0 ? (int) round(($stats['available'] * 100) / $stats['total']) : 0;
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $weekDays = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $weekSlots) {
        $date = $weekStart->copy()->addDays($offset);

        return (object) [
            'date' => $date,
            'slots' => $weekSlots->filter(fn ($slot) => $slot->date->isSameDay($date))->values(),
        ];
    });
    $grouped = $availabilities->getCollection()->groupBy(fn ($slot) => $slot->date->format('Y-m-d'));
    $hasSlots = $availabilities->total() > 0;
@endphp

@extends('layouts.doctor')
@section('page-title', 'Mes disponibilités')
@section('page-subtitle', 'Planning & disponibilités')

@section('page-actions')
    <a href="{{ route('doctor.availabilities.create') }}"
       class="hidden sm:inline-flex h-11 px-4 rounded-lg bg-[#2563eb] text-white hover:bg-[#004ac6] text-[14px] font-semibold items-center gap-2 shadow-md">
        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add</span>
        Ajouter une disponibilité
    </a>
@endsection

@section('doctor-content')
<div class="sc-stack w-full"
     x-data="{
        view: 'week',
        deleteOpen: false,
        deleteLabel: '',
        deleteAction: '',
        toast(message) {
            if (window.Alpine && Alpine.$data(document.body) && typeof Alpine.$data(document.body).showToast === 'function') {
                Alpine.$data(document.body).showToast(message);
            }
        },
        openDelete(label, action) {
            this.deleteLabel = label;
            this.deleteAction = action;
            this.deleteOpen = true;
        }
     }">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <nav aria-label="Fil d'Ariane" class="flex items-center gap-1.5 text-[#737686] text-[11px]">
                <span>Espace Praticien</span>
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                <span class="text-[#004ac6] font-semibold">Planning & Disponibilités</span>
            </nav>
            <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 tracking-tight">Mes disponibilités</h2>
            <p class="text-[15px] leading-6 text-[#434655] max-w-2xl">
                Configurez vos créneaux de consultation pour la prise de rendez-vous en ligne.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 self-start md:self-center">
            <button type="button"
                    class="h-11 px-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] hover:bg-[#e2e7ff] text-[14px] font-semibold inline-flex items-center gap-1.5 shadow-sm"
                    @click="toast('La synchronisation d’agenda n’est pas gérée.')">
                <span class="material-symbols-outlined text-[20px] text-[#006a61]" aria-hidden="true">sync_alt</span>
                <span>Synchronisation</span>
            </button>
            <a class="h-11 px-4 rounded-lg bg-[#2563eb] text-white hover:bg-[#004ac6] text-[14px] font-semibold inline-flex items-center gap-1.5 shadow-md"
               href="{{ route('doctor.availabilities.create') }}">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add</span>
                <span>Ajouter une disponibilité</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative overflow-hidden bg-white rounded-xl p-4 shadow-sm flex flex-col justify-between">
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-[#dbe1ff]/20 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] uppercase tracking-wider text-[#737686] font-semibold">Volume cette semaine</span>
                <div class="w-8 h-8 rounded-lg bg-[#dbe1ff] flex items-center justify-center text-[#00174b]">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">pace</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display text-[40px] leading-[48px] tracking-tight text-[#131b2e] font-extrabold">{{ $weekVolume }}</span>
                    <span class="text-[13px] text-[#434655] font-medium">{{ $weekVolumeUnit }}</span>
                </div>
                <p class="text-[13px] text-[#434655] mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[#007d55] text-[16px]" aria-hidden="true">check_circle</span>
                    {{ $weekSlots->count() }} créneau{{ $weekSlots->count() > 1 ? 'x' : '' }}
                    @if($weekDayCount > 0)
                        sur {{ $weekDayCount }} jour{{ $weekDayCount > 1 ? 's' : '' }}
                    @else
                        cette semaine
                    @endif
                </p>
            </div>
            <div class="mt-2 bg-[#f2f3ff] rounded-lg p-2 flex items-center justify-between text-[#737686] text-[11px]">
                <span>Taux d’occupation</span>
                <span class="text-[#004ac6] font-semibold">{{ $occupancy }}%</span>
            </div>
        </div>

        <div class="relative overflow-hidden bg-white rounded-xl p-4 shadow-sm flex flex-col justify-between">
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-[#89f5e7]/20 pointer-events-none"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] uppercase tracking-wider text-[#737686] font-semibold">Créneaux libres</span>
                <div class="w-8 h-8 rounded-lg bg-[#89f5e7] flex items-center justify-center text-[#00201d]">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">event_available</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1">
                    <span class="font-display text-[40px] leading-[48px] tracking-tight text-[#131b2e] font-extrabold">{{ $stats['available'] }}</span>
                    <span class="text-[13px] text-[#434655] font-medium">libre{{ $stats['available'] > 1 ? 's' : '' }}</span>
                </div>
                <p class="text-[13px] text-[#434655] mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[#006a61] text-[16px]" aria-hidden="true">timer</span>
                    {{ $stats['total'] }} créneau{{ $stats['total'] > 1 ? 'x' : '' }} au total
                </p>
            </div>
            <div class="mt-2 bg-[#f2f3ff] rounded-lg p-2 flex items-center justify-between text-[#737686] text-[11px]">
                <span>Réservés</span>
                <span class="text-[#006a61] font-semibold">{{ $stats['booked'] }}</span>
            </div>
        </div>

        <div class="relative overflow-hidden bg-white rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div class="flex flex-col justify-between h-full">
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-[#737686] font-semibold">Répartition</span>
                    <div class="mt-2 flex flex-col gap-1">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#2563eb]"></span>
                            <span class="text-[16px] leading-6 text-[#131b2e] font-bold">{{ $freeRate }}% libres</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#006a61]"></span>
                            <span class="text-[12px] text-[#434655] font-semibold">{{ $occupancy }}% réservés</span>
                        </div>
                    </div>
                </div>
                <span class="text-[11px] text-[#737686] mt-2">{{ $stats['booked'] }} réservé{{ $stats['booked'] > 1 ? 's' : '' }} / {{ $stats['available'] }} libre{{ $stats['available'] > 1 ? 's' : '' }}</span>
            </div>
            <div class="relative w-20 h-20 shrink-0" aria-hidden="true">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                    <path class="text-[#eaedff]" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3.8"></path>
                    <path class="text-[#006a61]" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="100, 100" stroke-width="3.8"></path>
                    <path class="text-[#2563eb]" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-dasharray="{{ $freeRate }}, 100" stroke-linecap="round" stroke-width="3.8"></path>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="material-symbols-outlined text-[20px] text-[#004ac6]">event_available</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm flex flex-col gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#f2f3ff] p-2 rounded-lg">
            <div class="flex items-center gap-1 bg-white p-1 rounded-lg shadow-sm" role="tablist" aria-label="Affichage">
                <button type="button"
                        class="px-4 py-1.5 rounded-lg text-[12px] font-semibold transition-all"
                        :class="view === 'week' ? 'bg-[#2563eb] text-white' : 'text-[#434655] hover:text-[#131b2e]'"
                        @click="view = 'week'"
                        :aria-selected="(view === 'week').toString()">
                    Vue Semaine
                </button>
                <button type="button"
                        class="px-4 py-1.5 rounded-lg text-[12px] font-semibold transition-all"
                        :class="view === 'list' ? 'bg-[#2563eb] text-white' : 'text-[#434655] hover:text-[#131b2e]'"
                        @click="view = 'list'"
                        :aria-selected="(view === 'list').toString()">
                    Vue Liste
                </button>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 text-[#737686] text-[11px]">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#007d55]"></span>
                    <span class="text-[#131b2e] font-medium">Semaine du {{ $weekStart->format('d/m/Y') }}</span>
                </div>
                <button type="button"
                        class="px-2 py-1.5 rounded-lg text-[#434655] hover:bg-[#e2e7ff] text-[11px] font-semibold inline-flex items-center gap-1"
                        @click="toast('La duplication de semaine n’est pas gérée.')">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">content_copy</span>
                    <span>Dupliquer semaine</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4" x-show="view === 'week'">
            @foreach($weekDays as $day)
                @php
                    $daySlots = $day->slots;
                    $freeCount = $daySlots->where('is_available', true)->count();
                    $bookedCount = $daySlots->where('is_available', false)->count();
                @endphp
                <div class="rounded-xl bg-[#f2f3ff]/60 hover:bg-[#f2f3ff] transition-all p-4 flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="w-full lg:w-44 shrink-0 flex items-center justify-between lg:flex-col lg:items-start gap-1">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $daySlots->isNotEmpty() ? 'bg-[#2563eb]' : 'bg-[#c3c6d7]' }}"></span>
                            <span class="text-[16px] leading-6 text-[#131b2e] font-bold">{{ $jours[$day->date->dayOfWeek] }}</span>
                        </div>
                        <span class="text-[11px] text-[#737686]">
                            {{ $day->date->format('d') }} {{ $mois[$day->date->month - 1] }}
                            ·
                            @if($daySlots->isEmpty())
                                aucune plage
                            @else
                                {{ $daySlots->count() }} plage{{ $daySlots->count() > 1 ? 's' : '' }}
                                @if($bookedCount > 0)
                                    · {{ $freeCount }} libre{{ $freeCount > 1 ? 's' : '' }}
                                @endif
                            @endif
                        </span>
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                        @forelse($daySlots as $slot)
                            @include('doctor.availabilities.partials.slot-card', ['slot' => $slot, 'slotMinutes' => $slotMinutes])
                        @empty
                            <a class="p-3 rounded-lg border border-dashed border-[#c3c6d7] bg-[#eaedff]/40 hover:bg-[#eaedff] flex items-center justify-center gap-1.5 text-[#434655] hover:text-[#004ac6] transition-all"
                               href="{{ route('doctor.availabilities.create') }}">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">add_circle</span>
                                <span class="text-[12px] font-semibold">Ajouter une plage</span>
                            </a>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4" x-show="view === 'list'" x-cloak>
            @if(! $hasSlots)
                <div class="rounded-xl bg-[#f2f3ff] p-10 text-center">
                    <span class="material-symbols-outlined text-6xl text-[#c3c6d7] mb-4 block" aria-hidden="true">event_busy</span>
                    <p class="text-[#131b2e] text-xl font-bold mb-2">Aucun créneau configuré</p>
                    <p class="text-[#434655] text-sm mb-6">Ajoutez vos disponibilités pour que les patients puissent vous réserver.</p>
                    <a href="{{ route('doctor.availabilities.create') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-[#2563eb] text-white rounded-lg font-semibold hover:bg-[#004ac6]">
                        <span class="material-symbols-outlined" aria-hidden="true">add</span>
                        Ajouter des créneaux
                    </a>
                </div>
            @else
                @forelse($grouped as $date => $slots)
                    @php $d = \Carbon\Carbon::parse($date); @endphp
                    <div class="rounded-xl bg-[#f2f3ff]/60 p-4 flex flex-col lg:flex-row lg:items-center gap-4">
                        <div class="w-full lg:w-44 shrink-0 flex items-center justify-between lg:flex-col lg:items-start gap-1">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#2563eb]"></span>
                                <span class="text-[16px] leading-6 text-[#131b2e] font-bold">{{ $jours[$d->dayOfWeek] }}</span>
                            </div>
                            <span class="text-[11px] text-[#737686]">
                                {{ $d->format('d') }} {{ $mois[$d->month - 1] }} {{ $d->format('Y') }}
                                · {{ $slots->count() }} créneau{{ $slots->count() > 1 ? 'x' : '' }}
                            </span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($slots->sortBy('start_time') as $slot)
                                @include('doctor.availabilities.partials.slot-card', ['slot' => $slot, 'slotMinutes' => $slotMinutes])
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-[13px] text-[#434655] px-1">Aucun créneau sur cette page.</p>
                @endforelse
            @endif
        </div>

        @if($hasSlots)
            <div class="bg-white p-4 rounded-2xl shadow-sm sc-pager">
                <p class="sc-pager__label">
                    Affichage de <span class="font-semibold text-[#131b2e]">{{ $availabilities->firstItem() }}</span>
                    à <span class="font-semibold text-[#131b2e]">{{ $availabilities->lastItem() }}</span>
                    sur <span class="font-semibold text-[#131b2e]">{{ $availabilities->total() }}</span> créneaux
                </p>
                <div class="sc-pager__pages">{{ $availabilities->links() }}</div>
            </div>
        @endif

        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-[#dbe1ff]/30 via-[#f2f3ff] to-[#89f5e7]/20 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#004ac6] shrink-0">
                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">beach_access</span>
                </div>
                <div>
                    <h3 class="text-[16px] leading-6 text-[#131b2e] font-bold">Bloquer une période d’absence</h3>
                    <p class="text-[13px] text-[#434655]">
                        Les créneaux déjà réservés restent maintenus. Pour fermer un jour, supprimez les créneaux libres.
                    </p>
                </div>
            </div>
            <button type="button"
                    class="whitespace-nowrap px-4 h-10 rounded-lg bg-white text-[#131b2e] hover:bg-[#e2e7ff] text-[12px] font-semibold shadow-sm inline-flex items-center gap-1.5 self-start sm:self-center"
                    @click="toast('Le blocage de congés n’est pas géré — supprimez les créneaux libres.')">
                <span class="material-symbols-outlined text-[18px] text-[#006242]" aria-hidden="true">event_busy</span>
                <span>Déclarer une absence</span>
            </button>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between text-[#737686] text-[11px] px-1 pb-2 gap-2">
        <div class="flex items-center gap-2">
            <span>Fuseau horaire : {{ config('app.timezone') }}</span>
        </div>
        <div class="flex items-center gap-4">
            <button type="button" class="hover:text-[#004ac6]" @click="toast('L’historique des modifications n’est pas géré.')">
                Consulter l’historique des modifications
            </button>
            <button type="button" class="hover:text-[#004ac6]" @click="toast('Aide sur le paramétrage non configurée.')">
                Aide sur le paramétrage
            </button>
        </div>
    </div>

    <div x-show="deleteOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#283044]/40 backdrop-blur-sm"
         role="dialog"
         aria-modal="true"
         aria-labelledby="delete-slot-title"
         @keydown.escape.window="deleteOpen = false">
        <div class="absolute inset-0" @click="deleteOpen = false" aria-hidden="true"></div>
        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-xl flex flex-col gap-4">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-lg bg-[#ffdad6] text-[#ba1a1a] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">warning</span>
                </div>
                <div>
                    <h3 id="delete-slot-title" class="text-[16px] leading-6 text-[#131b2e] font-bold">Supprimer cette plage horaire ?</h3>
                    <span class="text-[12px] text-[#004ac6] font-semibold" x-text="deleteLabel">Plage sélectionnée</span>
                </div>
            </div>
            <div class="bg-[#f2f3ff] p-3 rounded-lg">
                <p class="text-[13px] text-[#434655] leading-relaxed">
                    Ce créneau libre sera retiré. Les patients ne pourront plus le réserver. Un créneau déjà réservé ne peut pas être supprimé.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button"
                        class="px-4 h-10 rounded-lg bg-[#eaedff] text-[#131b2e] hover:bg-[#e2e7ff] text-[12px] font-semibold"
                        @click="deleteOpen = false">
                    Annuler
                </button>
                <form method="POST" :action="deleteAction">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 h-10 rounded-lg bg-[#ba1a1a] text-white hover:bg-[#93000a] text-[12px] font-semibold shadow-sm inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">delete</span>
                        <span>Confirmer la suppression</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
