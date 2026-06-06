@extends('layouts.app')
@section('title', 'Dashboard Patient')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Bonjour, {{ Auth::user()->name }}</h1>
            <p class="text-[#424752]">Voici le récapitulatif de votre santé aujourd'hui.</p>
        </div>
        <a href="/patient/doctors" class="flex items-center gap-2 px-6 py-3 bg-[#003f87] text-white rounded-xl font-medium hover:opacity-90 transition-all">
            <span class="material-symbols-outlined">add</span>
            Prendre RDV
        </a>
    </div>

    <div class="grid grid-cols-12 gap-6">

        {{-- Profile Card --}}
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="bg-white rounded-xl p-8 border border-[#c2c6d4]/30 shadow-sm">
                <div class="flex flex-col items-center text-center mb-8">
                    <div class="w-24 h-24 rounded-full bg-[#0056b3] text-white flex items-center justify-center text-4xl font-bold mb-4">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-semibold text-[#0d1c2f]">{{ Auth::user()->name }}</h2>
                    <span class="text-xs bg-[#e6eeff] text-[#003f87] px-3 py-1 rounded-full mt-2">Patient</span>
                </div>
                <div class="border-t border-[#c2c6d4] pt-6 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-[#424752]">Email</span>
                        <span class="text-sm font-medium text-[#0d1c2f]">{{ Auth::user()->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[#424752]">Total RDV</span>
                        <span class="text-sm font-medium text-[#0d1c2f]">{{ $appointments->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- Next Exam Widget --}}
            <div class="bg-[#0056b3] text-white rounded-xl p-6 shadow-md relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-sm opacity-80">Prochain RDV</h3>
                    @if($appointments->where('status', 'confirmed')->first())
                        <p class="text-xl font-semibold mt-2">
                            {{ $appointments->where('status', 'confirmed')->first()->date }}
                        </p>
                        <p class="text-sm opacity-90">
                            {{ $appointments->where('status', 'confirmed')->first()->time_slot }}
                        </p>
                    @else
                        <p class="text-xl font-semibold mt-2">Aucun RDV confirmé</p>
                    @endif
                </div>
                <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-[120px] opacity-10">medical_services</span>
            </div>
        </div>

        {{-- Appointments --}}
        <div class="col-span-12 lg:col-span-8 space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-[#0d1c2f]">Prochains Rendez-vous</h2>
                <a href="/patient/appointments" class="text-sm text-[#003f87] hover:underline">Voir tout l'historique</a>
            </div>

            @forelse($appointments->take(5) as $apt)
                <div class="bg-white rounded-xl p-6 border border-[#c2c6d4]/30 shadow-sm flex flex-col md:flex-row md:items-center gap-6">
                    <div class="w-16 h-16 bg-[#eff4ff] rounded-xl flex flex-col items-center justify-center text-[#003f87]">
                        <span class="text-xs font-semibold">{{ strtoupper(date('M', strtotime($apt->date))) }}</span>
                        <span class="text-2xl font-semibold">{{ date('d', strtotime($apt->date)) }}</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-bold text-[#0d1c2f]">
                                Dr. {{ $apt->doctor->user->name }}
                            </span>
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
                    @if($apt->status === 'pending')
                        <form action="/patient/appointments/{{ $apt->id }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="px-4 py-2 border border-[#ba1a1a] text-[#ba1a1a] rounded-lg text-sm hover:bg-[#ffdad6] transition-colors">
                                Annuler
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl p-8 border border-[#c2c6d4]/30 shadow-sm text-center text-[#424752]">
                    Aucun rendez-vous —
                    <a href="/patient/doctors" class="text-[#003f87] hover:underline">Prendre un RDV</a>
                </div>
            @endforelse

            {{-- Quick Actions --}}
            <div class="grid grid-cols-2 gap-6 pt-4">
                @foreach([
                    ['icon' => 'prescriptions', 'title' => 'Mes Ordonnances', 'desc' => 'Accédez à vos prescriptions numériques.'],
                    ['icon' => 'lab_panel', 'title' => "Résultats d'Analyses", 'desc' => 'Consultez vos derniers rapports de laboratoire.'],
                ] as $card)
                <div class="bg-white p-6 rounded-xl border border-[#c2c6d4]/30 shadow-sm hover:border-[#003f87] transition-all cursor-pointer group">
                    <div class="w-12 h-12 rounded-full bg-[#eff4ff] flex items-center justify-center text-[#003f87] mb-4 group-hover:bg-[#003f87] group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                    </div>
                    <h3 class="text-sm font-bold text-[#0d1c2f]">{{ $card['title'] }}</h3>
                    <p class="text-sm text-[#424752] mt-1">{{ $card['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
