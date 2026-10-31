@props(['appointment'])

@php
    $hex = hash('sha256', 'santeconnect-pass-'.$appointment->id);
    $code = strtoupper(substr($hex, 0, 12));
    $kiosk = implode('-', str_split($code, 4));
    $bits = str_split(substr(hash('sha256', (string) $appointment->id), 0, 36));
    $time = $appointment->availability
        ? substr($appointment->availability->start_time, 0, 5).' – '.substr($appointment->availability->end_time, 0, 5)
        : $appointment->appointment_date->format('H:i');
    $arriveBy = $appointment->availability
        ? \Carbon\Carbon::parse($appointment->availability->start_time)->subMinutes(15)->format('H:i')
        : null;
    $patient = $appointment->patient;
    $patientName = $patient->name ?? Auth::user()->name;
    $initials = collect(preg_split('/\s+/', trim($patientName)))->filter()->map(fn ($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('');
    $photo = $appointment->doctor->user->profile_image_url
        ?? ($appointment->doctor->photo ? asset('storage/'.$appointment->doctor->photo) : null);
    $patientMeta = collect([
        $patient?->age ? $patient->age.' ans' : null,
    ])->filter()->implode(' · ');
    $qrCells = [];
    foreach ($bits as $i => $bit) {
        if (hexdec($bit) % 2 === 0) {
            $col = 34 + (($i % 6) * 6);
            $row = 34 + ((int) floor($i / 6) * 6);
            if (($col >= 42 && $col <= 58 && $row >= 42 && $row <= 58) || $col > 88 || $row > 88) {
                continue;
            }
            $qrCells[] = [$col, $row, 5];
        }
    }
@endphp

<article {{ $attributes->merge(['class' => 'relative bg-white rounded-2xl shadow-md overflow-hidden']) }} aria-labelledby="pass-heading">
    <div class="h-2 w-full bg-gradient-to-r from-[#004ac6] via-[#2563eb] to-[#006a61]"></div>

    <div class="p-5 sm:p-8 flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#dbe1ff] flex items-center justify-center text-[#004ac6] shrink-0">
                    <span class="material-symbols-outlined" aria-hidden="true">badge</span>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#737686]">Accès médical prioritaire</p>
                    <h2 id="pass-heading" class="font-display text-[20px] leading-7 font-semibold text-[#131b2e]">Pass numérique du rendez-vous</h2>
                </div>
            </div>
            <div class="inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[#006242] text-[18px]" aria-hidden="true">verified</span>
                @include('patient.partials.status-badge', ['appointment' => $appointment])
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-[#f2f3ff] rounded-xl">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-14 h-14 rounded-full overflow-hidden ring-2 ring-white shadow-sm shrink-0 bg-[#131b2e] text-white flex items-center justify-center font-bold">
                    @if($photo)
                        <img src="{{ $photo }}" alt="Photo de Dr. {{ $appointment->doctor->user->name }}" class="w-full h-full object-cover">
                    @else
                        <span aria-hidden="true">{{ strtoupper(substr($appointment->doctor->user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#737686]">Médecin traitant</p>
                    <p class="font-display text-[16px] leading-6 font-bold text-[#131b2e] truncate">Dr. {{ $appointment->doctor->user->name }}</p>
                    <p class="text-[13px] text-[#434655] truncate">{{ $appointment->doctor->speciality->name }}</p>
                </div>
            </div>
            <div class="flex items-start gap-4 min-w-0 sm:pl-4">
                <div class="w-12 h-12 rounded-xl bg-[#eaedff] flex items-center justify-center text-[#131b2e] shrink-0 font-bold">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#737686]">Informations patient</p>
                    <p class="font-display text-[16px] leading-6 font-bold text-[#131b2e] truncate">{{ $patientName }}</p>
                    @if($patientMeta !== '')
                        <p class="text-[13px] text-[#434655]">{{ $patientMeta }}</p>
                    @endif
                    <p class="text-[11px] text-[#006a61] font-semibold mt-0.5">{{ $patient?->email }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 bg-[#eaedff] rounded-xl flex flex-col justify-between">
                <p class="text-[12px] font-medium text-[#434655]">Date</p>
                <div class="mt-2">
                    <p class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">{{ $appointment->appointment_date->translatedFormat('D j M') }}</p>
                    <p class="text-[13px] text-[#434655]">{{ $appointment->appointment_date->format('Y') }}</p>
                </div>
            </div>
            <div class="p-4 bg-[#dbe1ff] rounded-xl flex flex-col justify-between">
                <p class="text-[12px] font-medium text-[#003ea8]">Fenêtre horaire</p>
                <div class="mt-2">
                    <p class="font-display text-[20px] leading-7 font-bold text-[#004ac6]">{{ $time }}</p>
                    @if($arriveBy)
                        <p class="text-[11px] font-medium text-[#003ea8] mt-1">Arrivée conseillée {{ $arriveBy }}</p>
                    @endif
                </div>
            </div>
            <div class="p-4 bg-[#eaedff] rounded-xl min-w-0 flex flex-col justify-between">
                <p class="text-[12px] font-medium text-[#434655]">Lieu de consultation</p>
                <div class="mt-2">
                    <p class="font-display text-[16px] leading-6 font-bold text-[#131b2e] break-words">
                        {{ $appointment->doctor->address ?: 'Cabinet — adresse non renseignée' }}
                    </p>
                    <p class="text-[13px] text-[#434655]">Présentiel</p>
                </div>
            </div>
        </div>

        <div class="relative py-2 flex items-center justify-between">
            <div class="absolute -left-6 w-6 h-6 rounded-full bg-[#faf8ff] shadow-inner"></div>
            <div class="w-full flex justify-between gap-1 overflow-hidden px-2">
                @for($i = 0; $i < 20; $i++)
                    <span class="w-2 h-0.5 bg-[#dae2fd] rounded"></span>
                @endfor
            </div>
            <div class="absolute -right-6 w-6 h-6 rounded-full bg-[#faf8ff] shadow-inner"></div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-1">
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="relative w-28 h-28 p-2 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0" aria-hidden="true">
                    <svg class="w-full h-full text-[#131b2e]" fill="currentColor" viewBox="0 0 100 100">
                        <rect fill="none" height="28" rx="4" stroke="currentColor" stroke-width="4" width="28" x="2" y="2"></rect>
                        <rect height="16" rx="2" width="16" x="8" y="8"></rect>
                        <rect fill="none" height="28" rx="4" stroke="currentColor" stroke-width="4" width="28" x="70" y="2"></rect>
                        <rect height="16" rx="2" width="16" x="76" y="8"></rect>
                        <rect fill="none" height="28" rx="4" stroke="currentColor" stroke-width="4" width="28" x="2" y="70"></rect>
                        <rect height="16" rx="2" width="16" x="8" y="76"></rect>
                        <rect height="6" rx="1" width="6" x="36" y="6"></rect>
                        <rect height="6" rx="1" width="6" x="46" y="6"></rect>
                        <rect height="6" rx="1" width="6" x="56" y="16"></rect>
                        @foreach($qrCells as [$x, $y, $s])
                            <rect height="{{ $s }}" rx="1" width="{{ $s }}" x="{{ $x }}" y="{{ $y }}"></rect>
                        @endforeach
                        <circle cx="50" cy="50" fill="#ffffff" r="14"></circle>
                        <circle cx="50" cy="50" fill="#004ac6" r="12"></circle>
                        <rect fill="#ffffff" height="16" rx="1" width="5" x="47.5" y="42"></rect>
                        <rect fill="#ffffff" height="5" rx="1" width="16" x="42" y="47.5"></rect>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#737686]">Accueil &amp; référence</p>
                    <p class="text-[13px] text-[#434655] mt-1 max-w-xs">Présentez cette référence à l’accueil. Le QR est un motif visuel dérivé de l’identifiant, pas un pass Wallet.</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="font-mono text-xs tracking-widest text-[#131b2e] font-bold bg-[#eaedff] px-2 py-0.5 rounded">#{{ $appointment->id }}</span>
                        <button type="button"
                                class="text-[#004ac6] hover:underline text-xs font-semibold"
                                @click="copyText({{ \Illuminate\Support\Js::from((string) $appointment->id) }}, 'Référence copiée.')">
                            Copier
                        </button>
                    </div>
                    <p class="mt-1 font-mono text-[11px] text-[#737686]">{{ $kiosk }}</p>
                </div>
            </div>
            <div class="w-full sm:w-auto flex flex-col items-start sm:items-end gap-1 p-3 rounded-xl bg-[#f2f3ff]">
                <span class="inline-flex items-center gap-1 text-sm font-semibold text-[#006242]">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">domain</span>
                    Consultation en cabinet
                </span>
                <span class="text-xs text-[#434655]">Présentiel — seul mode géré par l’application.</span>
            </div>
        </div>
    </div>
</article>
