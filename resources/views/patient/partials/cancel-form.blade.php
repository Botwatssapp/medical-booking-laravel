@php
    $canCancel = in_array($appointment->status, ['pending', 'accepted'], true)
        && $appointment->appointment_date->isFuture();
    $label = $label ?? 'Annuler';
@endphp
@if($canCancel)
    <form method="POST" action="{{ route('patient.appointments.destroy', $appointment) }}" class="inline"
          onsubmit="return confirm('Confirmer l\'annulation de ce rendez-vous avec Dr. {{ $appointment->doctor->user->name }} le {{ $appointment->appointment_date->format('d/m/Y') }} ?')">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="{{ $buttonClass ?? 'flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-xs font-semibold transition-colors' }}">
            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">cancel</span>
            {{ $label }}
        </button>
    </form>
@endif
