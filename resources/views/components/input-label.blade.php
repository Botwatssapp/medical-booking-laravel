@props(['value'])

<label {{ $attributes->merge(['class' => 'sc-label']) }}>
    {{ $value ?? $slot }}
</label>
