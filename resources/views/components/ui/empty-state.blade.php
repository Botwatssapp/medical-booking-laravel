@props([
    'icon' => 'inbox',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'sc-card px-6 py-12 sm:py-16 text-center']) }}>
    <span class="material-symbols-outlined text-5xl sm:text-6xl text-[#c2c6d4] block mb-3" aria-hidden="true">{{ $icon }}</span>
    <p class="font-semibold text-[#0d1c2f]">{{ $title }}</p>
    @if($description)
        <p class="text-sm text-[#526069] mt-1">{{ $description }}</p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
