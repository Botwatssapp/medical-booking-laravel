@extends('layouts.app')
@section('title', 'Trouver un médecin')

@section('content')
<div class="max-w-[1200px] mx-auto px-8 py-10">

    {{-- En-tête --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#0d1c2f]">Trouver un médecin</h1>
        <p class="text-[#526069] mt-1">Consultez les profils et prenez rendez-vous en quelques clics.</p>
    </div>

    {{-- Barre de recherche --}}
    <form method="GET" action="{{ route('patient.doctors.index') }}"
          class="flex flex-wrap gap-3 mb-8 bg-white rounded-2xl border border-[#e0e7ff] px-5 py-4 shadow-sm">
        <div class="flex-1 min-w-[200px] relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#526069] text-[18px]">search</span>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher par nom…"
                   class="w-full pl-9 pr-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87]">
        </div>
        <select name="specialty_id"
                class="border border-[#c2c6d4] rounded-xl px-4 py-2.5 text-sm text-[#0d1c2f] focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 min-w-[180px]">
            <option value="">Toutes les spécialités</option>
            @foreach($specialties as $specialty)
                <option value="{{ $specialty->id }}" {{ request('specialty_id') == $specialty->id ? 'selected' : '' }}>
                    {{ $specialty->name }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="px-5 py-2.5 bg-[#003f87] hover:opacity-90 text-white text-sm font-semibold rounded-xl transition-opacity">
            Rechercher
        </button>
        @if(request()->hasAny(['search', 'specialty_id']))
            <a href="{{ route('patient.doctors.index') }}"
               class="px-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm text-[#526069] hover:border-[#003f87] hover:text-[#003f87] transition-colors">
                Réinitialiser
            </a>
        @endif
    </form>

    {{-- Grille des médecins --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($doctors as $doctor)
            <div class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm overflow-hidden hover:border-[#003f87]/40 hover:shadow-md transition-all group">

                {{-- Header card --}}
                <div class="bg-gradient-to-br from-[#eff4ff] to-[#f8faff] px-6 pt-6 pb-4 flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 bg-[#003f87] flex items-center justify-center">
                        @if($doctor->user->profile_image_url)
                            <img src="{{ $doctor->user->profile_image_url }}"
                                 alt="Dr. {{ $doctor->user->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-2xl font-bold">
                                {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-[#0d1c2f] truncate">Dr. {{ $doctor->user->name }}</p>
                        <span class="inline-block mt-1 px-2.5 py-0.5 bg-[#003f87]/10 text-[#003f87] rounded-full text-xs font-semibold">
                            {{ $doctor->speciality->name }}
                        </span>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4">
                    @if($doctor->bio)
                        <p class="text-sm text-[#526069] leading-relaxed line-clamp-2">{{ Str::limit($doctor->bio, 90) }}</p>
                    @else
                        <p class="text-sm text-[#c2c6d4] italic">Aucune biographie renseignée.</p>
                    @endif

                    <div class="mt-3 space-y-1.5">
                        @if($doctor->phone)
                            <div class="flex items-center gap-2 text-xs text-[#526069]">
                                <span class="material-symbols-outlined text-[14px] text-[#003f87]">phone</span>
                                {{ $doctor->phone }}
                            </div>
                        @endif
                        @if($doctor->address)
                            <div class="flex items-center gap-2 text-xs text-[#526069]">
                                <span class="material-symbols-outlined text-[14px] text-[#003f87]">location_on</span>
                                <span class="truncate">{{ $doctor->address }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Créneaux dispo --}}
                    @php
                        $slotCount = $doctor->availabilities()->where('is_available', true)->where('date', '>=', today())->count();
                    @endphp
                    <div class="mt-3 flex items-center gap-1.5 text-xs {{ $slotCount > 0 ? 'text-green-700' : 'text-[#526069]' }}">
                        <span class="material-symbols-outlined text-[14px]">{{ $slotCount > 0 ? 'event_available' : 'event_busy' }}</span>
                        {{ $slotCount > 0 ? $slotCount . ' créneau' . ($slotCount > 1 ? 'x disponibles' : ' disponible') : 'Aucun créneau disponible' }}
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 pb-5">
                    <a href="{{ route('patient.doctors.show', $doctor) }}"
                       class="w-full flex items-center justify-center gap-2 py-2.5 bg-[#003f87] hover:opacity-90 text-white text-sm font-semibold rounded-xl transition-opacity
                              {{ $slotCount === 0 ? 'opacity-60 pointer-events-none' : '' }}">
                        <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                        {{ $slotCount > 0 ? 'Prendre rendez-vous' : 'Voir le profil' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-2xl border border-[#e0e7ff] py-16 text-center shadow-sm">
                <span class="material-symbols-outlined text-6xl text-[#c2c6d4] block mb-4">stethoscope</span>
                <p class="font-semibold text-[#0d1c2f] mb-1">Aucun médecin trouvé</p>
                <p class="text-sm text-[#526069]">Essayez de modifier votre recherche.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $doctors->links() }}</div>
</div>
@endsection
