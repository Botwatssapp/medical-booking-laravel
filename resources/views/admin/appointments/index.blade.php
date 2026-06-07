@extends('layouts.admin')
@section('page-title', 'Rendez-vous')
@section('page-subtitle', 'Gestion et contrôle de tous les rendez-vous')

@section('admin-content')
<div class="space-y-6">

    {{-- ── Stat badges ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $statusFilters = [
                ['label' => 'Total',      'count' => $stats['total'],     'color' => 'bg-[#003f87]', 'val' => ''],
                ['label' => 'En attente', 'count' => $stats['pending'],   'color' => 'bg-yellow-500', 'val' => 'pending'],
                ['label' => 'Acceptés',   'count' => $stats['accepted'],  'color' => 'bg-green-500',  'val' => 'accepted'],
                ['label' => 'Annulés',    'count' => $stats['cancelled'], 'color' => 'bg-gray-500',   'val' => 'cancelled'],
            ];
        @endphp
        @foreach($statusFilters as $sf)
            <a href="{{ route('admin.appointments.index', ['status' => $sf['val']]) }}"
               class="bg-white rounded-2xl border {{ request('status') === $sf['val'] ? 'border-[#003f87] ring-2 ring-[#003f87]/20' : 'border-[#e0e7ff]' }} p-4 flex items-center gap-3 hover:border-[#003f87] transition-colors">
                <div class="w-3 h-3 rounded-full {{ $sf['color'] }} shrink-0"></div>
                <div>
                    <p class="text-xs text-[#526069]">{{ $sf['label'] }}</p>
                    <p class="text-2xl font-bold text-[#0d1c2f]">{{ $sf['count'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- ── Filters ── --}}
    <form method="GET" action="{{ route('admin.appointments.index') }}"
          class="bg-white rounded-2xl border border-[#e0e7ff] px-5 py-4 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#526069] text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher patient ou médecin…"
                       class="w-full pl-9 pr-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87]">
            </div>
        </div>
        <select name="status" class="border border-[#c2c6d4] rounded-xl px-4 py-2.5 text-sm text-[#0d1c2f] focus:outline-none focus:ring-2 focus:ring-[#003f87]/30">
            <option value="">Tous les statuts</option>
            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>En attente</option>
            <option value="accepted"  {{ request('status') === 'accepted'  ? 'selected' : '' }}>Accepté</option>
            <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Refusé</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
            <option value="missed"    {{ request('status') === 'missed'    ? 'selected' : '' }}>Non réalisé</option>
        </select>
        <button type="submit"
                class="px-5 py-2.5 bg-[#003f87] hover:opacity-90 text-white text-sm font-semibold rounded-xl transition-opacity">
            Filtrer
        </button>
        @if(request()->hasAny(['status', 'search']))
            <a href="{{ route('admin.appointments.index') }}"
               class="px-4 py-2.5 border border-[#c2c6d4] rounded-xl text-sm text-[#526069] hover:border-[#003f87] hover:text-[#003f87] transition-colors">
                Réinitialiser
            </a>
        @endif
    </form>

    {{-- ── Table ── --}}
    <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8faff] border-b border-[#e0e7ff]">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Patient</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Médecin</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f4ff]">
                @forelse($appointments as $appointment)
                    @php
                        $badge = match($appointment->status) {
                            'pending'   => 'bg-yellow-100 text-yellow-800',
                            'accepted'  => 'bg-green-100 text-green-800',
                            'rejected'  => 'bg-red-100 text-red-800',
                            'cancelled' => 'bg-gray-100 text-gray-700',
                            'completed' => 'bg-blue-100 text-blue-800',
                            'missed'    => 'bg-orange-100 text-orange-800',
                            default     => 'bg-gray-100 text-gray-700',
                        };
                        $statusLabel = match($appointment->status) {
                            'pending'   => 'En attente',
                            'accepted'  => 'Accepté',
                            'rejected'  => 'Refusé',
                            'cancelled' => 'Annulé',
                            'completed' => 'Terminé',
                            'missed'    => 'Non réalisé',
                            default     => ucfirst($appointment->status),
                        };
                        $canCancel = !in_array($appointment->status, ['cancelled', 'completed', 'missed']);
                    @endphp
                    <tr class="hover:bg-[#f8faff] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($appointment->patient->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#0d1c2f]">{{ $appointment->patient->name }}</p>
                                    <p class="text-xs text-[#526069]">{{ $appointment->patient->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-[#0d1c2f]">Dr {{ $appointment->doctor->user->name }}</p>
                            <p class="text-xs text-[#526069]">{{ $appointment->doctor->speciality->name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-[#0d1c2f]">{{ $appointment->appointment_date->format('d/m/Y') }}</p>
                            @if($appointment->availability)
                                <p class="text-xs text-[#526069]">{{ \Carbon\Carbon::parse($appointment->availability->start_time)->format('H:i') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.appointments.show', $appointment) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Détails
                                </a>
                                @if($canCancel)
                                    <form method="POST"
                                          action="{{ route('admin.appointments.cancel', $appointment) }}"
                                          class="inline"
                                          onsubmit="return confirm('Annuler ce rendez-vous ?')">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs font-semibold text-red-600 hover:underline">
                                            Annuler
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-[#526069]">
                            <span class="material-symbols-outlined text-4xl text-[#c2c6d4] block mb-2">calendar_month</span>
                            Aucun rendez-vous trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    {{ $appointments->links() }}
</div>
@endsection
