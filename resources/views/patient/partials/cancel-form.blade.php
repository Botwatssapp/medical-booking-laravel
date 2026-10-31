@php
    $canCancel = in_array($appointment->status, ['pending', 'accepted'], true)
        && $appointment->appointment_date->isFuture();
    $label = $label ?? 'Annuler';
@endphp
@if($canCancel)
    <div class="inline" x-data="{ open: false }">
        <button type="button"
                @click="open = true"
                class="{{ $buttonClass ?? 'flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-xs font-semibold transition-colors' }}">
            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">cancel</span>
            {{ $label }}
        </button>
        <x-ui.modal name="open" title="Confirmer l’annulation">
            <p class="text-sm text-[#5b6573] mb-5">
                Confirmer l'annulation de ce rendez-vous avec Dr. {{ $appointment->doctor->user->name }}
                le {{ $appointment->appointment_date->format('d/m/Y') }} ?
            </p>
            <div class="flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" class="sc-btn-secondary" @click="open = false">Retour</button>
                <form method="POST" action="{{ route('patient.appointments.destroy', $appointment) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="sc-btn-danger w-full">{{ $label }}</button>
                </form>
            </div>
        </x-ui.modal>
    </div>
@endif
