@extends('layouts.doctor')
@section('page-title', 'Ajouter des disponibilités')
@section('page-subtitle', 'Définissez vos créneaux horaires de consultation')

@section('doctor-content')
<div class="max-w-2xl">

    {{-- Back --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('doctor.availabilities.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87] transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Retour aux disponibilités
        </a>
    </div>

    {{-- Onglets --}}
    <div class="flex bg-[#f0f2f8] p-1 rounded-xl mb-6 gap-1">
        <button type="button" id="tab-btn-single"
            class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all tab-active">
            <span class="material-symbols-outlined text-base">add_circle</span>
            Créneau unique
        </button>
        <button type="button" id="tab-btn-generate"
            class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all tab-inactive">
            <span class="material-symbols-outlined text-base">auto_fix_high</span>
            Génération automatique
        </button>
    </div>

    {{-- Erreurs de validation --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
            <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- PANNEAU 1 : Créneau unique                              --}}
    {{-- ======================================================= --}}
    <div id="panel-single">
        <form method="POST" action="{{ route('doctor.availabilities.store') }}"
              class="bg-white rounded-2xl shadow-sm border border-[#c2c6d4]/30 p-6 space-y-5">
            @csrf
            <input type="hidden" name="mode" value="single">

            <div>
                <label for="s-date" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" id="s-date" name="date"
                    value="{{ old('date') }}"
                    min="{{ now()->addDay()->format('Y-m-d') }}"
                    required
                    class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                           focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="s-start" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                        Heure de début <span class="text-red-500">*</span>
                    </label>
                    <input type="time" id="s-start" name="start_time"
                        value="{{ old('start_time') }}"
                        required
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                               focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    @error('start_time')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="s-end" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                        Heure de fin <span class="text-red-500">*</span>
                    </label>
                    <input type="time" id="s-end" name="end_time"
                        value="{{ old('end_time') }}"
                        required
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                               focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    @error('end_time')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Aperçu durée unique --}}
            <div id="single-preview" class="hidden bg-[#eff4ff] border border-[#003f87]/20 rounded-xl px-4 py-3">
                <p class="text-sm text-[#003f87]">
                    <span class="material-symbols-outlined align-middle text-base mr-1">schedule</span>
                    Créneau : <strong id="single-preview-text"></strong>
                </p>
            </div>

            <button type="submit"
                class="w-full py-3 bg-[#003f87] text-white rounded-xl font-semibold hover:opacity-90 transition-opacity">
                Ajouter ce créneau
            </button>
        </form>
    </div>

    {{-- ======================================================= --}}
    {{-- PANNEAU 2 : Génération automatique                      --}}
    {{-- ======================================================= --}}
    <div id="panel-generate" class="hidden">
        <form method="POST" action="{{ route('doctor.availabilities.store') }}"
              class="bg-white rounded-2xl shadow-sm border border-[#c2c6d4]/30 p-6 space-y-5">
            @csrf
            <input type="hidden" name="mode" value="generate">

            {{-- Info --}}
            <div class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-blue-800">
                <span class="material-symbols-outlined text-blue-500 shrink-0 mt-0.5">info</span>
                <p>Choisissez une plage horaire et une durée par consultation. Les créneaux seront créés automatiquement en ignorant ceux qui existent déjà.</p>
            </div>

            <div>
                <label for="g-date" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" id="g-date" name="date"
                    value="{{ old('date') }}"
                    min="{{ now()->addDay()->format('Y-m-d') }}"
                    required
                    class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                           focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="g-start" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                        Début de la plage <span class="text-red-500">*</span>
                    </label>
                    <input type="time" id="g-start" name="period_start"
                        value="{{ old('period_start', '08:00') }}"
                        required
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                               focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    @error('period_start')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="g-end" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                        Fin de la plage <span class="text-red-500">*</span>
                    </label>
                    <input type="time" id="g-end" name="period_end"
                        value="{{ old('period_end', '18:00') }}"
                        required
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                               focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    @error('period_end')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Durée par créneau --}}
            <div>
                <label class="block text-sm font-semibold text-[#0d1c2f] mb-2">
                    Durée par consultation <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-5 gap-2">
                    @foreach([15 => '15 min', 20 => '20 min', 30 => '30 min', 45 => '45 min', 60 => '1h'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="duration_minutes" value="{{ $val }}"
                                class="sr-only dur-radio"
                                {{ old('duration_minutes', 30) == $val ? 'checked' : '' }}>
                            <div class="dur-pill text-center py-2.5 rounded-xl border text-sm font-semibold transition-all select-none
                                        {{ old('duration_minutes', 30) == $val
                                            ? 'bg-[#003f87] text-white border-[#003f87]'
                                            : 'border-[#c2c6d4] text-[#0d1c2f] hover:border-[#003f87] hover:text-[#003f87]' }}">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('duration_minutes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Aperçu dynamique --}}
            <div id="preview-area" class="hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-[#0d1c2f]">
                        Aperçu — <span id="preview-count" class="text-[#003f87]">0</span> créneau(x)
                    </p>
                </div>
                <div id="preview-slots" class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-3 bg-[#f8f9ff] rounded-xl border border-[#c2c6d4]/30"></div>
            </div>

            <button type="submit"
                class="w-full py-3 bg-[#003f87] text-white rounded-xl font-semibold hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined align-middle mr-1">auto_fix_high</span>
                Générer les créneaux
            </button>
        </form>
    </div>

</div>

<style>
.tab-active  { background: white; color: #003f87; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
.tab-inactive { color: #526069; }
</style>

<script>
// ── Onglets ──────────────────────────────────────────────────────────────────
const btnSingle   = document.getElementById('tab-btn-single');
const btnGenerate = document.getElementById('tab-btn-generate');
const panelSingle   = document.getElementById('panel-single');
const panelGenerate = document.getElementById('panel-generate');

function activateTab(tab) {
    const isSingle = tab === 'single';
    btnSingle.className   = btnSingle.className.replace(/tab-active|tab-inactive/g, '').trim()
        + (isSingle ? ' tab-active' : ' tab-inactive');
    btnGenerate.className = btnGenerate.className.replace(/tab-active|tab-inactive/g, '').trim()
        + (!isSingle ? ' tab-active' : ' tab-inactive');
    panelSingle.classList.toggle('hidden', !isSingle);
    panelGenerate.classList.toggle('hidden', isSingle);
}

btnSingle.addEventListener('click',   () => activateTab('single'));
btnGenerate.addEventListener('click', () => activateTab('generate'));

// Remettre l'onglet "generate" si mode=generate après erreur de validation
@if(old('mode') === 'generate')
    activateTab('generate');
@endif

// ── Durée — style radio ──────────────────────────────────────────────────────
document.querySelectorAll('.dur-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.dur-pill').forEach((pill, i) => {
            const r = document.querySelectorAll('.dur-radio')[i];
            pill.className = pill.className
                .replace('bg-[#003f87] text-white border-[#003f87]', '')
                .replace('border-[#c2c6d4] text-[#0d1c2f] hover:border-[#003f87] hover:text-[#003f87]', '')
                .trim();
            if (r.checked) {
                pill.classList.add('bg-[#003f87]', 'text-white', 'border-[#003f87]');
            } else {
                pill.classList.add('border-[#c2c6d4]', 'text-[#0d1c2f]', 'hover:border-[#003f87]', 'hover:text-[#003f87]');
            }
        });
        updatePreview();
    });
});

// ── Aperçu créneau unique ────────────────────────────────────────────────────
function updateSinglePreview() {
    const s = document.getElementById('s-start').value;
    const e = document.getElementById('s-end').value;
    const preview = document.getElementById('single-preview');
    const text    = document.getElementById('single-preview-text');
    if (s && e && s < e) {
        text.textContent = s + ' → ' + e;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}
document.getElementById('s-start').addEventListener('change', updateSinglePreview);
document.getElementById('s-end').addEventListener('change', updateSinglePreview);

// ── Aperçu génération automatique ───────────────────────────────────────────
function pad(n) { return String(n).padStart(2, '0'); }

function updatePreview() {
    const s   = document.getElementById('g-start').value;
    const e   = document.getElementById('g-end').value;
    const dur = parseInt(document.querySelector('.dur-radio:checked')?.value || 0);
    const area    = document.getElementById('preview-area');
    const slots   = document.getElementById('preview-slots');
    const counter = document.getElementById('preview-count');

    if (!s || !e || !dur) { area.classList.add('hidden'); return; }

    const [sh, sm] = s.split(':').map(Number);
    const [eh, em] = e.split(':').map(Number);
    let cur = sh * 60 + sm;
    const end = eh * 60 + em;

    if (cur >= end) { area.classList.add('hidden'); return; }

    slots.innerHTML = '';
    let count = 0;

    while (cur + dur <= end) {
        const slotEnd = cur + dur;
        const tag = document.createElement('span');
        tag.className = 'px-3 py-1.5 bg-[#eff4ff] text-[#003f87] rounded-lg text-xs font-semibold border border-[#003f87]/10';
        tag.textContent =
            pad(Math.floor(cur / 60)) + ':' + pad(cur % 60) + ' → ' +
            pad(Math.floor(slotEnd / 60)) + ':' + pad(slotEnd % 60);
        slots.appendChild(tag);
        cur = slotEnd;
        count++;
    }

    counter.textContent = count;
    area.classList.toggle('hidden', count === 0);
}

document.getElementById('g-start').addEventListener('change', updatePreview);
document.getElementById('g-end').addEventListener('change', updatePreview);

// Déclencher l'aperçu au chargement
updatePreview();
updateSinglePreview();
</script>
@endsection
