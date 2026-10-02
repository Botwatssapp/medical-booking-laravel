@extends('layouts.admin')
@section('page-title', 'Détail du rendez-vous')
@section('page-subtitle', $appointment->patient->name.' · Dr '.$appointment->doctor->user->name)

@section('admin-content')
<div class="max-w-3xl space-y-6">

    <a href="{{ route('admin.appointments.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87] transition-colors">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
        Retour aux rendez-vous
    </a>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-3">Patient</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($appointment->patient->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-[#0d1c2f]">{{ $appointment->patient->name }}</p>
                    <p class="text-sm text-[#526069]">{{ $appointment->patient->email }}</p>
                    @if($appointment->patient->phone)
                        <p class="text-sm text-[#526069]">{{ $appointment->patient->phone }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-3">Médecin</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($appointment->doctor->user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-[#0d1c2f]">Dr {{ $appointment->doctor->user->name }}</p>
                    <p class="text-sm text-[#526069]">{{ $appointment->doctor->speciality->name }}</p>
                    <p class="text-sm text-[#526069]">{{ $appointment->doctor->user->email }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
        <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-4">Informations</p>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
            <div>
                <dt class="text-xs text-[#526069] mb-0.5">Date</dt>
                <dd class="font-semibold text-[#0d1c2f]">{{ $appointment->appointment_date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-[#526069] mb-0.5">Heure</dt>
                <dd class="font-semibold text-[#0d1c2f]">
                    @if($appointment->availability)
                        {{ substr($appointment->availability->start_time, 0, 5) }}
                        – {{ substr($appointment->availability->end_time, 0, 5) }}
                    @else
                        {{ $appointment->appointment_date->format('H:i') }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs text-[#526069] mb-0.5">Statut actuel</dt>
                <dd>@include('admin.partials.status-badge', ['appointment' => $appointment])</dd>
            </div>
            <div>
                <dt class="text-xs text-[#526069] mb-0.5">Créé le</dt>
                <dd class="font-semibold text-[#0d1c2f]">{{ $appointment->created_at->format('d/m/Y à H:i') }}</dd>
            </div>
        </dl>

        @if($appointment->notes)
            <div class="mt-4 pt-4 border-t border-[#e0e7ff]">
                <p class="text-xs text-[#526069] mb-1">Notes du patient</p>
                <p class="text-sm text-[#0d1c2f] bg-[#f8faff] rounded-xl px-4 py-3 border border-[#e0e7ff] whitespace-pre-line">
                    {{ $appointment->notes }}
                </p>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
        <p class="text-xs font-bold text-[#526069] uppercase tracking-wider mb-4">
            Contrôle administrateur
        </p>

        <div class="space-y-4">
            <form method="POST" action="{{ route('admin.appointments.updateStatus', $appointment) }}"
                  class="flex items-center gap-3 flex-wrap">
                @csrf @method('PATCH')
                <label for="admin-status" class="text-sm font-medium text-[#0d1c2f] shrink-0">Changer le statut</label>
                <select id="admin-status" name="status"
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

            @if(! in_array($appointment->status, ['cancelled', 'completed', 'missed'], true))
                <div class="border-t border-[#e0e7ff] pt-4">
                    <form method="POST"
                          action="{{ route('admin.appointments.cancel', $appointment) }}"
                          onsubmit="return confirm('Confirmer l\'annulation de ce rendez-vous ?')">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2 px-5 py-2.5 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 text-sm font-semibold rounded-xl transition-colors">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">cancel</span>
                            Annuler ce rendez-vous
                        </button>
                    </form>
                    <p class="text-xs text-[#526069] mt-2">
                        L’annulation passe par le service de rendez-vous et libère le créneau s’il n’est plus occupé.
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
