@extends('layouts.app')
@section('title', 'Liste des Médecins')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-[#0d1c2f] mb-2">Liste des Médecins</h1>
        <p class="text-[#424752]">Trouvez le spécialiste adapté à vos besoins.</p>
    </div>

    {{-- Search --}}
    <form action="/patient/doctors" method="GET" class="flex flex-col md:flex-row gap-4 mb-10">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">search</span>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Nom du médecin..."
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-white outline-none text-sm"
            />
        </div>
        <select
            name="speciality"
            class="px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-white outline-none text-sm"
        >
            <option value="">Toutes les spécialités</option>
            @foreach($specialities as $spec)
                <option value="{{ $spec->id }}" {{ request('speciality') == $spec->id ? 'selected' : '' }}>
                    {{ $spec->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="px-8 py-3 bg-[#003f87] text-white rounded-xl font-medium hover:opacity-90 transition-all">
            Rechercher
        </button>
    </form>

    {{-- Doctors Grid --}}
    @if($doctors->isEmpty())
        <div class="bg-white rounded-xl p-8 text-center text-[#424752] border border-[#c2c6d4]/30">
            Aucun médecin trouvé.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($doctors as $doctor)
                <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-[#c2c6d4] group">
                    <div class="h-48 bg-[#eff4ff] flex items-center justify-center">
                        <div class="w-24 h-24 rounded-full bg-[#0056b3] text-white flex items-center justify-center text-4xl font-bold">
                            {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#0d1c2f]">Dr. {{ $doctor->user->name }}</h3>
                        <p class="text-sm text-[#003f87] font-medium mb-3">{{ $doctor->speciality->name }}</p>
                        @if($doctor->address)
                            <div class="flex items-center gap-2 text-[#424752] text-sm mb-4">
                                <span class="material-symbols-outlined text-lg">location_on</span>
                                {{ $doctor->address }}
                            </div>
                        @endif
                        @if($doctor->bio)
                            <p class="text-xs text-[#526069] mb-4">{{ Str::limit($doctor->bio, 80) }}</p>
                        @endif
                        <div class="pt-4 border-t border-[#c2c6d4]">

                                href="/patient/doctors/{{ $doctor->id }}/book"
                                class="w-full block text-center px-4 py-2 bg-[#0056b3] text-white rounded-lg text-sm font-medium hover:opacity-90 transition-all"
                            >
                                Réserver un RDV
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
