@extends('layouts.app')
@section('title', 'Mes Rendez-vous')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-3xl font-bold text-[#0d1c2f] mb-2">Mes Rendez-vous</h1>
            <p class="text-[#424752]">Historique de tous vos rendez-vous médicaux.</p>
        </div>
        <a href="/patient/doctors" class="flex items-center gap-2 px-6 py-3 bg-[#003f87] text-white rounded-xl font-medium hover:opacity-90 transition-all">
            <span class="material-symbols-outlined">add</span>
            Nouveau RDV
        </a>
    </div>

    {{-- Appointments --}}
    @forelse($appointments as $apt)
        <div class="bg-white rounded-xl p-6 border border-[#c2c6d4]/30 shadow-sm flex flex-col md:flex-row md:items-center gap-6 mb-4">

            {{-- Date --}}
            <div class="w-16 h-16 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87] shrink-0">
                <span class="text-xs font-semibold">{{ strtoupper(date('M', strtotime($apt->date))) }}</span>
                <span class="text-2xl font-semibold">{{ date('d', strtotime($apt->date)) }}</span>
            </div>

            {{-- Info --}}
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-sm font-bold text-[#0d1c2f]">Dr. {{ $apt->doctor->user->name }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold
                        @if($apt->status === 'confirmed') bg-green-100 text-green-700
                        @elseif($apt->status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($apt->status === 'cancelled') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($apt->status) }}
                    </span>
                </div>
                <p class="text-sm text-[#424752]">
                    {{ $apt->doctor->speciality->name }} — {{ $apt->time_slot }}
                </p>
                @if($apt->reason)
                    <p class="text-xs text-[#526069] mt-1">{{ $apt->reason }}</p>
                @endif
            </div>

            {{-- Actions --}}
            @if($apt->status === 'pending')
                <div class="flex gap-3 shrink-0">
                    <button
                        onclick="document.getElementById('edit-{{ $apt->id }}').classList.toggle('hidden')"
                        class="px-4 py-2 bg-[#eff4ff] text-[#003f87] rounded-lg text-sm font-medium hover:bg-[#dde9ff] transition-colors"
                    >
                        Déplacer
                    </button>
                    <form action="/patient/appointments/{{ $apt->id }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-4 py-2 border border-[#ba1a1a] text-[#ba1a1a] rounded-lg text-sm font-medium hover:bg-[#ffdad6] transition-colors">
                            Annuler
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Edit Form --}}
        @if($apt->status === 'pending')
            <div id="edit-{{ $apt->id }}" class="hidden bg-[#eff4ff] rounded-xl p-6 border border-[#dde9ff] mb-4">
                <form action="/patient/appointments/{{ $apt->id }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                    @csrf
                    @method('PATCH')
                    <div class="flex-1 space-y-2">
                        <label class="text-sm font-medium text-[#0d1c2f]">Nouvelle date</label>
                        <input
                            type="date"
                            name="date"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-white outline-none text-sm"
                        />
                    </div>
                    <div class="flex-1 space-y-2">
                        <label class="text-sm font-medium text-[#0d1c2f]">Nouveau créneau</label>
                        <select name="time_slot" class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-white outline-none text-sm">
                            @foreach(['09:00','09:30','10:00','10:30','11:00','14:00','14:30','15:00','15:30','16:00'] as $slot)
                                <option value="{{ $slot }}">{{ $slot }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-[#003f87] text-white rounded-xl text-sm font-medium hover:opacity-90 transition-all">
                        Confirmer
                    </button>
                </form>
            </div>
        @endif
    @empty
        <div class="bg-white rounded-xl p-12 border border-[#c2c6d4]/30 shadow-sm text-center">
            <span class="material-symbols-outlined text-6xl text-[#c2c6d4] mb-4 block">calendar_today</span>
            <p class="text-[#424752] mb-4">Aucun rendez-vous trouvé</p>
            <a href="/patient/doctors" class="px-6 py-3 bg-[#003f87] text-white rounded-xl text-sm font-medium hover:opacity-90 transition-all">
                Prendre un RDV
            </a>
        </div>
    @endforelse
</div>
@endsection
