@extends('layouts.app')
@section('title', 'Trouver un médecin')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-8 py-8 sm:py-10">

    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#0d1c2f]">Trouver un médecin</h1>
        <p class="text-[#526069] mt-1">Consultez les profils validés et réservez un créneau disponible.</p>
    </div>

    <form method="GET" action="{{ route('patient.doctors.index') }}"
          class="flex flex-col sm:flex-row flex-wrap gap-3 mb-8 bg-white rounded-2xl border border-[#e0e7ff] px-4 sm:px-5 py-4 shadow-sm"
          role="search">
        <div class="flex-1 min-w-[200px] relative">
            <label for="doctor-search" class="sr-only">Rechercher un médecin</label>
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#526069] text-[18px]" aria-hidden="true">search</span>
            <input type="text" id="doctor-search" name="search" value="{{ request('search') }}"
                   placeholder="Nom du médecin ou spécialité"
                   class="w-full pl-9 pr-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87]">
        </div>
        <div>
            <label for="doctor-speciality" class="sr-only">Filtrer par spécialité</label>
            <select id="doctor-speciality" name="speciality"
                    class="w-full sm:w-auto border border-[#c2c6d4] rounded-xl px-4 py-2.5 text-sm text-[#0d1c2f] focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 min-w-[180px]">
                <option value="">Toutes les spécialités</option>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" {{ (string) request('speciality', request('specialty_id')) === (string) $specialty->id ? 'selected' : '' }}>
                        {{ $specialty->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="px-5 py-2.5 bg-[#003f87] hover:opacity-90 text-white text-sm font-semibold rounded-xl transition-opacity">
            Rechercher
        </button>
        @if($hasFilters)
            <a href="{{ route('patient.doctors.index') }}"
               class="px-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm text-[#526069] hover:border-[#003f87] hover:text-[#003f87] transition-colors text-center">
                Réinitialiser
            </a>
        @endif
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($doctors as $doctor)
            @php $slotCount = $doctor->available_slots_count; @endphp
            <article class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm overflow-hidden hover:border-[#003f87]/40 hover:shadow-md transition-all flex flex-col">
                <div class="bg-gradient-to-br from-[#eff4ff] to-[#f8faff] px-6 pt-6 pb-4 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 bg-[#003f87] flex items-center justify-center">
                        @if($doctor->user->profile_image_url)
                            <img src="{{ $doctor->user->profile_image_url }}"
                                 alt="Photo de Dr. {{ $doctor->user->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-2xl font-bold" aria-hidden="true">
                                {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-bold text-[#0d1c2f] truncate">Dr. {{ $doctor->user->name }}</h2>
                        <span class="inline-block mt-1 px-2.5 py-0.5 bg-[#003f87]/10 text-[#003f87] rounded-full text-xs font-semibold">
                            {{ $doctor->speciality->name }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-4 flex-1">
                    @if($doctor->bio)
                        <p class="text-sm text-[#526069] leading-relaxed line-clamp-2">{{ Str::limit($doctor->bio, 90) }}</p>
                    @else
                        <p class="text-sm text-[#c2c6d4] italic">Aucune biographie renseignée.</p>
                    @endif

                    <div class="mt-3 space-y-1.5">
                        @if($doctor->address)
                            <div class="flex items-center gap-2 text-xs text-[#526069]">
                                <span class="material-symbols-outlined text-[14px] text-[#003f87]" aria-hidden="true">location_on</span>
                                <span class="truncate">{{ $doctor->address }}</span>
                            </div>
                        @endif
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs {{ $slotCount > 0 ? 'text-green-800' : 'text-[#526069]' }}">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">{{ $slotCount > 0 ? 'event_available' : 'event_busy' }}</span>
                        {{ $slotCount > 0 ? $slotCount.' créneau'.($slotCount > 1 ? 'x disponibles' : ' disponible') : 'Aucun créneau disponible' }}
                    </p>
                </div>

                <div class="px-6 pb-5">
                    <a href="{{ route('patient.doctors.show', $doctor) }}"
                       class="w-full flex items-center justify-center gap-2 py-2.5 bg-[#003f87] hover:opacity-90 text-white text-sm font-semibold rounded-xl transition-opacity">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">calendar_month</span>
                        {{ $slotCount > 0 ? 'Prendre rendez-vous' : 'Voir le profil' }}
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-[#e0e7ff] py-16 text-center shadow-sm">
                <span class="material-symbols-outlined text-6xl text-[#c2c6d4] block mb-4" aria-hidden="true">stethoscope</span>
                @if($hasFilters)
                    <p class="font-semibold text-[#0d1c2f] mb-1">Aucun médecin ne correspond à votre recherche</p>
                    <p class="text-sm text-[#526069] mb-4">Modifiez le nom ou la spécialité, puis relancez la recherche.</p>
                    <a href="{{ route('patient.doctors.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-[#003f87] text-white rounded-xl text-sm font-semibold">
                        Voir tous les médecins
                    </a>
                @else
                    <p class="font-semibold text-[#0d1c2f] mb-1">Aucun médecin disponible</p>
                    <p class="text-sm text-[#526069]">Revenez plus tard : de nouveaux profils seront ajoutés après validation.</p>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $doctors->links() }}</div>
</div>
@endsection
