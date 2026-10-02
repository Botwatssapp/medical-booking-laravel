@extends('layouts.doctor')
@section('page-title', 'Détail du rendez-vous')
@section('page-subtitle', 'Demande de ' . $appointment->patient->name)

@section('doctor-content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('doctor.appointments.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87] transition-colors">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
        Retour aux rendez-vous
    </a>

    <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden shadow-sm">
        <div class="flex items-center justify-between gap-3 px-4 sm:px-6 py-4 border-b border-[#e0e7ff] bg-[#f8faff]">
            <span class="font-bold text-[#0d1c2f]">Statut</span>
            @include('doctor.partials.status-badge', ['appointment' => $appointment])
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            @php $p = $appointment->patient; @endphp
            <div class="p-4 bg-[#f8faff] rounded-xl border border-[#e0e7ff]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#003f87] flex items-center justify-center text-white text-lg font-bold shrink-0 overflow-hidden">
                        @if($p->profile_image_url)
                            <img src="{{ $p->profile_image_url }}" class="w-full h-full object-cover" alt="Photo de {{ $p->name }}">
                        @else
                            <span aria-hidden="true">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-[#0d1c2f]">{{ $p->name }}</p>
                        @if($p->email)
                            <p class="text-sm text-[#526069]">{{ $p->email }}</p>
                        @endif
                        @if($p->phone)
                            <p class="text-sm text-[#526069]">{{ $p->phone }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                    <p class="text-[#0d1c2f] text-sm leading-relaxed whitespace-pre-line">{{ $appointment->notes }}</p>
                </div>
            @endif

            <p class="text-xs text-[#526069]">Demande reçue le {{ $appointment->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        @if($appointment->status === 'pending')
            <div class="flex flex-col sm:flex-row gap-3 px-4 sm:px-6 pb-6">
                @include('doctor.partials.pending-actions', ['appointment' => $appointment])
            </div>
        @endif

        @if($appointment->status === 'accepted')
            <div class="space-y-3 px-4 sm:px-6 pb-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit"
                                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl flex items-center justify-center gap-2">
                            Marquer terminé
                        </button>
                    </form>
                    <form method="POST" action="{{ route('doctor.appointments.reschedule', $appointment) }}" class="flex-1"
                          onsubmit="return confirm('Reporter {{ addslashes($appointment->patient->name) }} ({{ $appointment->appointment_date->format('d/m/Y H:i') }}) sur votre prochain créneau libre ?')">
                        @csrf
                        <button type="submit"
                                class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl flex items-center justify-center gap-2">
                            Reporter
                        </button>
                    </form>
                </div>
                <form method="POST" action="{{ route('doctor.appointments.destroy', $appointment) }}"
                      onsubmit="return confirm('Annuler ce rendez-vous avec {{ addslashes($appointment->patient->name) }} ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-3 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 font-semibold rounded-xl">
                        Annuler le rendez-vous
                    </button>
                </form>
                <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3 text-sm text-amber-800">
                    <strong>Reporter</strong> annule ce rendez-vous et crée automatiquement un nouveau
                    rendez-vous confirmé sur votre premier créneau disponible.
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
