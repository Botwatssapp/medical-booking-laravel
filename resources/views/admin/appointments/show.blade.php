@extends('layouts.admin')
@section('page-title', 'Détail du rendez-vous')
@section('page-subtitle', 'Consultation et gestion du rendez-vous #' . $appointment->id)

@section('admin-content')
<div class="max-w-3xl space-y-6">

    {{-- Back --}}
    <a href="{{ route('admin.appointments.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87] transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Retour aux rendez-vous
    </a>

    {{-- ── Parties concernées ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Patient --}}
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-3">Patient</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($appointment->patient->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-[#0d1c2f]">{{ $appointment->patient->name }}</p>
                    <p class="text-sm text-[#526069]">{{ $appointment->patient->email }}</p>
                </div>
            </div>
        </div>

        {{-- Médecin --}}
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-3">Médecin</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($appointment->doctor->user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-[#0d1c2f]">Dr {{ $appointment->doctor->user->name }}</p>
                    <p class="text-sm text-[#526069]">{{ $appointment->doctor->speciality->name }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Détails du rendez-vous ── --}}
    <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
        <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-4">Informations</p>

        <dl class="grid grid-cols-2 gap-x-8 gap-y-4">
            <div>
                <dt class="text-xs text-[#526069] mb-0.5">Date</dt>
                <dd class="font-semibold text-[#0d1c2f]">{{ $appointment->appointment_date->format('d/m/Y') }}</dd>
            </div>
            @if($appointment->availability)
                <div>
                    <dt class="text-xs text-[#526069] mb-0.5">Heure</dt>
                    <dd class="font-semibold text-[#0d1c2f]">
                        {{ \Carbon\Carbon::parse($appointment->availability->start_time)->format('H:i') }}
                        – {{ \Carbon\Carbon::parse($appointment->availability->end_time)->format('H:i') }}
                    </dd>
                </div>
            @endif
            <div>
                <dt class="text-xs text-[#526069] mb-0.5">Statut actuel</dt>
                <dd>
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
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badge }}">{{ $statusLabel }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-[#526069] mb-0.5">Créé le</dt>
                <dd class="font-semibold text-[#0d1c2f]">{{ $appointment->created_at->format('d/m/Y à H:i') }}</dd>
            </div>
        </dl>

        @if($appointment->notes)
            <div class="mt-4 pt-4 border-t border-[#e0e7ff]">
                <dt class="text-xs text-[#526069] mb-1">Notes du patient</dt>
                <dd class="text-sm text-[#0d1c2f] bg-[#f8faff] rounded-xl px-4 py-3 border border-[#e0e7ff]">
                    {{ $appointment->notes }}
                </dd>
            </div>
        @endif
    </div>

    {{-- ── Contrôle admin ── --}}
    <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
        <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-4">
            <span class="inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-[#003f87]">admin_panel_settings</span>
                Contrôle administrateur
            </span>
        </p>

        <div class="space-y-4">

            {{-- Changer le statut --}}
            <form method="POST" action="{{ route('admin.appointments.updateStatus', $appointment) }}"
                  class="flex items-center gap-3 flex-wrap">
                @csrf @method('PATCH')
                <label class="text-sm font-medium text-[#0d1c2f] shrink-0">Changer le statut :</label>
                <select name="status"
                        class="border border-[#c2c6d4] rounded-xl px-4 py-2.5 text-sm text-[#0d1c2f] focus:outline-none focus:ring-2 focus:ring-[#003f87]/30">
                    <option value="pending"   {{ $appointment->status === 'pending'   ? 'selected' : '' }}>En attente</option>
                    <option value="accepted"  {{ $appointment->status === 'accepted'  ? 'selected' : '' }}>Accepté</option>
                    <option value="rejected"  {{ $appointment->status === 'rejected'  ? 'selected' : '' }}>Refusé</option>
                    <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Terminé</option>
                    <option value="missed"    {{ $appointment->status === 'missed'    ? 'selected' : '' }}>Non réalisé</option>
                </select>
                <button type="submit"
                        class="px-5 py-2.5 bg-[#003f87] hover:opacity-90 text-white text-sm font-semibold rounded-xl transition-opacity">
                    Appliquer
                </button>
            </form>

            {{-- Annulation rapide --}}
            @if(!in_array($appointment->status, ['cancelled', 'completed', 'missed']))
                <div class="border-t border-[#e0e7ff] pt-4">
                    <form method="POST"
                          action="{{ route('admin.appointments.cancel', $appointment) }}"
                          onsubmit="return confirm('Confirmer l\'annulation de ce rendez-vous ?')">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2 px-5 py-2.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 text-sm font-semibold rounded-xl transition-colors">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            Annuler ce rendez-vous
                        </button>
                    </form>
                    <p class="text-xs text-[#526069] mt-2">
                        L'annulation libèrera automatiquement le créneau horaire du médecin.
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
