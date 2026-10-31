@props(['title' => 'Aperçu démo'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900']) }} role="note">
    <p class="font-semibold">{{ $title }}</p>
    <p class="mt-1 leading-relaxed">{{ $slot }}</p>
</div>
