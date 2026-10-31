@php
    $start = substr((string) $slot->start_time, 0, 5);
    $end = substr((string) $slot->end_time, 0, 5);
    $minutes = $slotMinutes($slot);
    $label = $start.' - '.$end;
@endphp
<div class="p-3 bg-white rounded-lg shadow-sm flex items-center justify-between gap-2 {{ $slot->is_available ? '' : 'opacity-90' }}">
    <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 rounded-lg {{ $slot->is_available ? 'bg-[#e2e7ff] text-[#004ac6]' : 'bg-[#dae2fd] text-[#737686]' }} flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">{{ $slot->is_available ? 'medical_services' : 'lock' }}</span>
        </div>
        <div class="flex flex-col min-w-0">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[16px] leading-6 text-[#131b2e] font-bold tracking-tight">{{ $label }}</span>
                @if($slot->is_available)
                    <span class="px-2 py-0.5 rounded-full bg-[#6ffbbe] text-[#002113] text-[11px] font-semibold">Libre</span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-[#dae2fd] text-[#434655] text-[11px] font-semibold">Réservé</span>
                @endif
            </div>
            <span class="text-[13px] text-[#434655]">
                {{ $minutes > 0 ? $minutes.' min' : 'Créneau' }}
            </span>
        </div>
    </div>
    <div class="flex items-center gap-0.5 shrink-0">
        @if($slot->is_available)
            <button type="button"
                    class="p-1.5 rounded-lg hover:bg-[#ffdad6] text-[#737686] hover:text-[#ba1a1a] transition-all"
                    title="Supprimer"
                    aria-label="Supprimer le créneau de {{ $start }}"
                    @click="openDelete(@js($label), @js(route('doctor.availabilities.destroy', $slot)))">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>
            </button>
        @else
            <span class="w-8 h-8 flex items-center justify-center rounded-lg text-[#c3c6d7]"
                  title="Créneau réservé — non supprimable">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">lock</span>
            </span>
        @endif
    </div>
</div>
