@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Tableau de bord</h1>
            <p class="text-[#424752]">Vue d'ensemble de la plateforme.</p>
        </div>
        <div class="flex gap-3">
            <a href="/admin/doctors" class="px-4 py-2 bg-[#003f87] text-white rounded-xl text-sm font-medium hover:opacity-90">
                Gérer Médecins
            </a>
            <a href="/admin/specialities" class="px-4 py-2 bg-white border border-[#c2c6d4] text-[#003f87] rounded-xl text-sm font-medium hover:bg-[#eff4ff]">
                Spécialités
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
        @foreach([
            ['label' => 'Patients',    'value' => $stats['patients'],     'icon' => 'person',          'color' => 'bg-[#eff4ff] text-[#003f87]'],
            ['label' => 'Médecins',    'value' => $stats['doctors'],      'icon' => 'stethoscope',      'color' => 'bg-[#e6f9ff] text-[#006882]'],
            ['label' => 'Total RDV',   'value' => $stats['appointments'], 'icon' => 'calendar_month',   'color' => 'bg-[#f3f0ff] text-[#5b21b6]'],
            ['label' => 'En attente',  'value' => $stats['pending'],      'icon' => 'pending',          'color' => 'bg-yellow-50 text-yellow-700'],
            ['label' => 'Confirmés',   'value' => $stats['confirmed'],    'icon' => 'check_circle',     'color' => 'bg-green-50 text-green-700'],
            ['label' => 'Annulés',     'value' => $stats['cancelled'],    'icon' => 'cancel',           'color' => 'bg-red-50 text-red-700'],
        ] as $stat)
            <div class="bg-white rounded-xl p-5 border border-[#c2c6d4]/30 shadow-sm">
                <div class="w-10 h-10 rounded-lg {{ $stat['color'] }} flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-xl">{{ $stat['icon'] }}</span>
                </div>
                <p class="text-3xl font-bold text-[#0d1c2f]">{{ $stat['value'] }}</p>
                <p class="text-xs text-[#424752] mt-1">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Quick Nav --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        @foreach([
            ['href' => '/admin/users',        'icon' => 'group',          'label' => 'Utilisateurs'],
            ['href' => '/admin/doctors',       'icon' => 'stethoscope',    'label' => 'Médecins'],
            ['href' => '/admin/specialities',  'icon' => 'category',       'label' => 'Spécialités'],
            ['href' => '/admin/appointments',  'icon' => 'calendar_month', 'label' => 'Rendez-vous'],
        ] as $nav)
            <a href="{{ $nav['href'] }}" class="bg-white rounded-xl p-6 border border-[#c2c6d4]/30 shadow-sm hover:border-[#003f87] hover:shadow-md transition-all group flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#eff4ff] text-[#003f87] flex items-center justify-center group-hover:bg-[#003f87] group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined">{{ $nav['icon'] }}</span>
                </div>
                <span class="font-medium text-[#0d1c2f]">{{ $nav['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Recent Appointments --}}
    <h2 class="text-xl font-semibold text-[#0d1c2f] mb-6">Derniers Rendez-vous</h2>

    <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8f9ff] border-b border-[#c2c6d4]">
                <tr>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Patient</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Médecin</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Date</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Heure</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#c2c6d4]/30">
                @forelse($appointments as $apt)
                    <tr class="hover:bg-[#f8f9ff] transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-[#0d1c2f]">{{ $apt->patient->name }}</td>
                        <td class="px-6 py-4 text-sm text-[#424752]">Dr. {{ $apt->doctor->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ date('d/m/Y', strtotime($apt->date)) }}</td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ $apt->time_slot }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                @if($apt->status === 'confirmed') bg-green-100 text-green-700
                                @elseif($apt->status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($apt->status === 'cancelled') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($apt->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#424752]">Aucun rendez-vous</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
