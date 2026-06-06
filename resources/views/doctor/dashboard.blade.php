@extends('layouts.app')
@section('title', 'Dashboard Médecin')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Bonjour, Dr. {{ Auth::user()->name }}</h1>
            <p class="text-[#424752]">Voici l'aperçu de votre journée.</p>
        </div>
        <a href="/doctor/profile" class="flex items-center gap-2 px-6 py-3 bg-white border border-[#c2c6d4] text-[#003f87] rounded-xl font-medium hover:bg-[#eff4ff] transition-all">
            <span class="material-symbols-outlined">manage_accounts</span>
            Mon Profil
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        @foreach([
            ['label' => 'Total RDV',   'value' => $stats['total'],     'icon' => 'calendar_month',  'color' => 'bg-[#eff4ff] text-[#003f87]'],
            ['label' => 'En attente',  'value' => $stats['pending'],   'icon' => 'pending',          'color' => 'bg-yellow-50 text-yellow-700'],
            ['label' => 'Confirmés',   'value' => $stats['confirmed'], 'icon' => 'check_circle',     'color' => 'bg-green-50 text-green-700'],
            ['label' => 'Refusés',     'value' => $stats['refused'],   'icon' => 'cancel',           'color' => 'bg-red-50 text-red-700'],
        ] as $stat)
            <div class="bg-white rounded-xl p-6 border border-[#c2c6d4]/30 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg {{ $stat['color'] }} flex items-center justify-center">
                        <span class="material-symbols-outlined">{{ $stat['icon'] }}</span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-[#0d1c2f]">{{ $stat['value'] }}</p>
                <p class="text-sm text-[#424752] mt-1">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Appointments --}}
    <h2 class="text-xl font-semibold text-[#0d1c2f] mb-6">Demandes de rendez-vous</h2>

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
                    <span class="text-sm font-bold text-[#0d1c2f]">{{ $apt->patient->name }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold
                        @if($apt->status === 'confirmed') bg-green-100 text-green-700
                        @elseif($apt->status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($apt->status === 'cancelled') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($apt->status) }}
                    </span>
                </div>
                <p class="text-sm text-[#424752]">{{ $apt->time_slot }}</p>
                @if($apt->reason)
                    <p class="text-xs text-[#526069] mt-1">{{ $apt->reason }}</p>
                @endif
            </div>

            {{-- Actions --}}
            @if($apt->status === 'pending')
                <div class="flex gap-3 shrink-0">
                    <form action="/doctor/appointments/{{ $apt->id }}/confirm" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                            Confirmer
                        </button>
                    </form>
                    <form action="/doctor/appointments/{{ $apt->id }}/refuse" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                            Refuser
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-xl p-12 border border-[#c2c6d4]/30 shadow-sm text-center">
            <span class="material-symbols-outlined text-6xl text-[#c2c6d4] mb-4 block">calendar_today</span>
            <p class="text-[#424752]">Aucun rendez-vous pour le moment</p>
        </div>
    @endforelse
</div>
@endsection
