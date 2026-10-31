@php
    $photo = $appointment->doctor->user->profile_image_url ?? ($appointment->doctor->photo ? asset('storage/'.$appointment->doctor->photo) : null);
    $time = $appointment->availability
        ? substr($appointment->availability->start_time, 0, 5)
        : $appointment->appointment_date->format('H:i');
    $bookAgain = route('patient.doctors.show', $appointment->doctor);
@endphp
<div class="p-4 bg-[#f2f3ff] rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-4 min-w-0">
        <div class="w-12 h-12 rounded-full overflow-hidden bg-[#e2e7ff] flex items-center justify-center shrink-0">
            @if($photo)
                <img src="{{ $photo }}" alt="Photo de Dr. {{ $appointment->doctor->user->name }}" class="w-full h-full object-cover">
            @else
                <span class="text-sm font-bold text-[#004ac6]" aria-hidden="true">{{ strtoupper(substr($appointment->doctor->user->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="min-w-0 space-y-0.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-display text-[16px] leading-6 font-semibold truncate">Dr. {{ $appointment->doctor->user->name }}</span>
                @include('patient.partials.status-badge', ['appointment' => $appointment])
            </div>
            <p class="text-[13px] text-[#434655]">{{ $appointment->doctor->speciality->name }}{{ $appointment->doctor->address ? ' • '.$appointment->doctor->address : '' }}</p>
            <p class="text-[11px] text-[#737686] flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">event</span>
                {{ $appointment->appointment_date->format('d/m/Y') }} • {{ $time }}
            </p>
        </div>
    </div>
    <div class="flex items-center justify-between md:justify-end gap-3 shrink-0">
        <a href="{{ route('patient.appointments.show', $appointment) }}" class="p-2 text-[#434655] hover:text-[#004ac6] hover:bg-[#e2e7ff] rounded-lg" title="Voir le détail">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">visibility</span>
            <span class="sr-only">Détail</span>
        </a>
        @if($appointment->status === 'completed')
            <a href="{{ $bookAgain }}" class="px-3 py-1.5 bg-[#eaedff] text-[#131b2e] hover:bg-[#dae2fd] rounded-lg text-[11px] font-medium">Réserver à nouveau</a>
        @else
            @include('patient.partials.cancel-form', ['appointment' => $appointment])
        @endif
    </div>
</div>
