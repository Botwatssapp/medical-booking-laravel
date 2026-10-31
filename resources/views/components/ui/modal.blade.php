@props([
    'name' => 'open',
    'title' => '',
    'maxWidth' => 'max-w-lg',
])

<div x-show="{{ $name }}"
     x-cloak
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-6"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="{{ $name }} = false">
    <div class="sc-overlay" @click="{{ $name }} = false" aria-hidden="true"></div>
    <div class="relative w-full {{ $maxWidth }} sc-card p-5 sm:p-6 mx-0 sm:mx-auto rounded-t-3xl sm:rounded-3xl max-h-[92vh] overflow-y-auto">
        <div class="flex items-start justify-between gap-3 mb-4">
            <h2 class="text-lg font-bold text-[#0b1220]">{{ $title }}</h2>
            <button type="button" class="sc-btn-ghost !min-h-9 !px-2" @click="{{ $name }} = false" aria-label="Fermer">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
