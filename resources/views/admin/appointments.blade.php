@extends('layouts.app')
@section('title', 'Tous les Rendez-vous')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-10">
        <a href="/admin/dashboard" class="flex items-center gap-2 text-[#003f87] hover:underline">
            <span class="material-symbols-outlined">arrow_back</span>
            Retour
        </a>
        <h1 class="text-3xl font-bold text-[#0d1c2f]">Tous les Rendez-vous</h1>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label' => 'Total',      'value' => $stats['total'],     'color' => 'bg-[#eff4ff] text-[#003f87]'],
            ['label' => 'En attente', 'value' => $stats['pending'],   'color' => 'bg-yellow-50 text-yellow-700'],
            ['label' => 'Confirmés',  'value' => $stats['confirmed'], 'color' => 'bg-green-50 text-green-700'],
            ['label' => 'Annulés',    'value' => $stats['cancelled'], 'color' => 'bg-red-50 text-red-700'],
        ] as $stat)
            <div class="bg-white rounded-xl p-5 border border-[#c2c6d4]/30 shadow-sm">
                <p class="text-3xl font-bold text-[#0d1c2f]">{{ $stat['value'] }}</p>
                <p class="text-xs text-[#424752] mt-1">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8f9ff] border-b border-[#c2c6d4]">
                <tr>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Patient</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Médecin</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Date</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Heure</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Motif</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#c2c6d4]/30">
                @forelse($appointments as $apt)
                    <tr class="hover:bg-[#f8f9ff] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#eff4ff] text-[#003f87] flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($apt->patient->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-[#0d1c2f]">{{ $apt->patient->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#424752]">Dr. {{ $apt->doctor->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ date('d/m/Y', strtotime($apt->date)) }}</td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ $apt->time_slot }}</td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ $apt->reason ?? '—' }}</td>
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
                        <td colspan="6" class="px-6 py-8 text-center text-[#424752]">Aucun rendez-vous</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-[#c2c6d4]">
            {{ $appointments->links() }}
        </div>
    </div>
</div>
@endsection
