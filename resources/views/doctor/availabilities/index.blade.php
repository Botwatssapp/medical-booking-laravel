@extends('layouts.doctor')
@section('page-title', 'Mes disponibilités')
@section('page-subtitle', 'Gérez vos créneaux horaires de consultation')

@section('page-actions')
    <a href="{{ route('doctor.availabilities.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Ajouter des créneaux
    </a>
@endsection

@section('doctor-content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-5 border border-[#c2c6d4]/30 shadow-sm">
            <p class="text-sm text-[#526069] mb-1">Total créneaux</p>
            <p class="text-3xl font-bold text-[#0d1c2f]">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl p-5 border border-green-100 shadow-sm">
            <p class="text-sm text-green-700 mb-1">Libres</p>
            <p class="text-3xl font-bold text-green-800">{{ $stats['available'] }}</p>
        </div>
        <div class="bg-red-50 rounded-xl p-5 border border-red-100 shadow-sm">
            <p class="text-sm text-red-700 mb-1">Réservés</p>
            <p class="text-3xl font-bold text-red-800">{{ $stats['booked'] }}</p>
        </div>
    </div>

    {{-- Créneaux groupés par date --}}
    @php
        $grouped = $availabilities->getCollection()->groupBy(fn ($a) => $a->date->format('Y-m-d'));
        $jours   = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $mois    = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
    @endphp

    @forelse($grouped as $date => $slots)
        @php $d = \Carbon\Carbon::parse($date); @endphp

        <div class="mb-8">
            {{-- En-tête de date --}}
            <div class="flex items-center gap-4 mb-3">
                <div class="w-14 h-14 bg-[#003f87] rounded-xl flex flex-col items-center justify-center text-white shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider leading-none">
                        {{ $mois[$d->month - 1] }}
                    </span>
                    <span class="text-2xl font-bold leading-tight">{{ $d->format('d') }}</span>
                </div>
                <div>
                    <p class="font-bold text-[#0d1c2f]">
                        {{ $jours[$d->dayOfWeek] }} {{ $d->format('d') }} {{ $mois[$d->month - 1] }} {{ $d->format('Y') }}
                    </p>
                    <p class="text-sm text-[#526069]">
                        {{ $slots->count() }} créneau{{ $slots->count() > 1 ? 'x' : '' }}
                        &mdash;
                        {{ $slots->where('is_available', true)->count() }} libre{{ $slots->where('is_available', true)->count() > 1 ? 's' : '' }}
                    </p>
                </div>
            </div>

            {{-- Grille des créneaux --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 pl-[4.5rem]">
                @foreach($slots->sortBy('start_time') as $slot)
                    <div class="bg-white rounded-xl border border-[#c2c6d4]/40 shadow-sm px-4 py-3
                                flex items-center justify-between gap-3
                                {{ !$slot->is_available ? 'opacity-70' : '' }}">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-lg text-[#003f87] shrink-0">schedule</span>
                            <div class="min-w-0">
                                <p class="font-semibold text-[#0d1c2f] text-sm">
                                    {{ substr($slot->start_time, 0, 5) }}
                                    <span class="text-[#526069] font-normal">→</span>
                                    {{ substr($slot->end_time, 0, 5) }}
                                </p>
                                <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                    {{ $slot->is_available ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $slot->is_available ? 'Libre' : 'Réservé' }}
                                </span>
                            </div>
                        </div>

                        {{-- Suppression (seulement si libre) --}}
                        @if($slot->is_available)
                            <form method="POST"
                                  action="{{ route('doctor.availabilities.destroy', $slot) }}"
                                  class="inline shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Supprimer ce créneau"
                                    onclick="return confirm('Supprimer ce créneau ?')">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </form>
                        @else
                            <span class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 shrink-0"
                                  title="Créneau réservé — non supprimable">
                                <span class="material-symbols-outlined text-sm">lock</span>
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl p-16 border border-[#c2c6d4]/30 shadow-sm text-center">
            <span class="material-symbols-outlined text-7xl text-[#c2c6d4] mb-5 block">event_busy</span>
            <p class="text-[#0d1c2f] text-xl font-bold mb-2">Aucun créneau configuré</p>
            <p class="text-[#526069] text-sm mb-8">
                Ajoutez vos disponibilités pour que les patients puissent vous réserver.
            </p>
            <a href="{{ route('doctor.availabilities.create') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-[#003f87] text-white rounded-xl font-semibold hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined">add</span>
                Ajouter des créneaux
            </a>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div>{{ $availabilities->links() }}</div>

</div>
@endsection
