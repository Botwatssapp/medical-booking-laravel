@extends('layouts.doctor')
@section('page-title', 'Détail du rendez-vous')
@section('page-subtitle', 'Demande de ' . $appointment->patient->name)

@section('doctor-content')
<div class="max-w-2xl space-y-5">

    {{-- Back --}}
    <a href="{{ route('doctor.appointments.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87] transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Retour aux rendez-vous
    </a>

    {{-- Carte principale --}}
    <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden shadow-sm">

        {{-- Bandeau statut --}}
        @php
            [$bgClass, $textClass, $label, $icon] = match($appointment->status) {
                'pending'   => ['bg-yellow-50',  'text-yellow-800', 'En attente de confirmation', 'pending'],
                'accepted'  => ['bg-green-50',   'text-green-800',  'Confirmé',                   'check_circle'],
                'rejected'  => ['bg-red-50',     'text-red-800',    'Refusé',                     'cancel'],
                'cancelled' => ['bg-gray-50',    'text-gray-600',   'Annulé',                     'event_busy'],
                'completed' => ['bg-blue-50',    'text-blue-800',   'Terminé',                    'task_alt'],
                'missed'    => ['bg-orange-50',  'text-orange-800', 'Manqué',                     'running_with_errors'],
                default     => ['bg-gray-50',    'text-gray-600',   ucfirst($appointment->status), 'info'],
            };
        @endphp
        <div class="flex items-center gap-3 px-6 py-4 {{ $bgClass }} border-b border-[#e0e7ff]">
            <span class="material-symbols-outlined {{ $textClass }}">{{ $icon }}</span>
            <span class="font-bold {{ $textClass }}">{{ $label }}</span>
        </div>

        {{-- Détails --}}
        <div class="p-6 space-y-4">

            {{-- Patient --}}
            <div class="flex items-center gap-4 p-4 bg-[#f8faff] rounded-xl border border-[#e0e7ff]">
                <div class="w-12 h-12 rounded-full bg-[#003f87] flex items-center justify-center text-white text-lg font-bold shrink-0">
                    {{ strtoupper(substr($appointment->patient->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-[#0d1c2f]">{{ $appointment->patient->name }}</p>
                    <p class="text-sm text-[#526069]">{{ $appointment->patient->email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-[#f8faff] rounded-xl p-4 border border-[#e0e7ff]">
                    <p class="text-xs font-bold text-[#526069] uppercase tracking-wide mb-1">Date</p>
                    <p class="font-bold text-[#0d1c2f]">{{ $appointment->appointment_date->format('d/m/Y') }}</p>
                </div>
                <div class="bg-[#f8faff] rounded-xl p-4 border border-[#e0e7ff]">
                    <p class="text-xs font-bold text-[#526069] uppercase tracking-wide mb-1">Heure</p>
                    @if($appointment->availability)
                        <p class="font-bold text-[#0d1c2f]">
                            {{ substr($appointment->availability->start_time, 0, 5) }}
                            – {{ substr($appointment->availability->end_time, 0, 5) }}
                        </p>
                    @else
                        <p class="font-bold text-[#0d1c2f]">{{ $appointment->appointment_date->format('H:i') }}</p>
                    @endif
                </div>
            </div>

            @if($appointment->notes)
                <div class="p-4 bg-[#f8faff] rounded-xl border border-[#e0e7ff]">
                    <p class="text-xs font-bold text-[#526069] uppercase tracking-wide mb-2">Notes du patient</p>
                    <p class="text-[#0d1c2f] text-sm leading-relaxed">{{ $appointment->notes }}</p>
                </div>
            @endif

            <p class="text-xs text-[#526069]">Demande reçue le {{ $appointment->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        {{-- Actions --}}
        @if($appointment->status === 'pending')
            <div class="flex gap-3 px-6 pb-6">
                <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="flex-1">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="accepted">
                    <button type="submit"
                        class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        Accepter
                    </button>
                </form>
                <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="flex-1">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit"
                        class="w-full py-3 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">cancel</span>
                        Refuser
                    </button>
                </form>
            </div>
        @endif

        @if($appointment->status === 'accepted')
            <div class="space-y-3 px-6 pb-6">
                <div class="flex gap-3">
                    <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit"
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">task_alt</span>
                            Marquer terminé
                        </button>
                    </form>
                    <form method="POST" action="{{ route('doctor.appointments.reschedule', $appointment) }}" class="flex-1"
                          onsubmit="return confirm('Reporter {{ addslashes($appointment->patient->name) }} sur votre prochain créneau disponible ?')">
                        @csrf
                        <button type="submit"
                            class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">event_repeat</span>
                            Reporter
                        </button>
                    </form>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3 text-sm text-amber-800 flex items-start gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-base shrink-0 mt-0.5">info</span>
                    <span>
                        <strong>Reporter</strong> annule ce rendez-vous et crée automatiquement un nouveau
                        rendez-vous pour le patient sur votre premier créneau disponible.
                    </span>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
