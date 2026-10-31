@props([
    'name' => null,
    'id' => null,
    'icon' => null,
    'label' => null,
    'plain' => false,
])

@php
    $fieldId = $id ?? $name ?? ('sc-select-'.uniqid());
@endphp

<div class="sc-select-wrap {{ $plain ? 'is-plain' : '' }}"
     x-data="scSelect()"
     @keydown.escape.window="open = false"
     @click.outside="open = false">
    @if($label)
        <label for="{{ $fieldId }}" class="sr-only">{{ $label }}</label>
    @endif
    <select id="{{ $fieldId }}"
            @if($name) name="{{ $name }}" @endif
            x-ref="native"
            class="sr-only"
            {{ $attributes }}>
        {{ $slot }}
    </select>
    <button type="button"
            class="sc-select-trigger"
            :class="open && 'is-open'"
            @click="toggle()"
            :aria-expanded="open.toString()"
            aria-haspopup="listbox"
            @if($label) aria-label="{{ $label }}" @endif>
        @if($icon)
            <span class="material-symbols-outlined text-[18px] text-[#737686] shrink-0" aria-hidden="true">{{ $icon }}</span>
        @endif
        <span class="flex-1 truncate" x-text="label"></span>
        <span class="material-symbols-outlined text-[20px] text-[#737686] shrink-0 transition-transform" :class="open && 'rotate-180'" aria-hidden="true">expand_more</span>
    </button>
    <ul x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        class="sc-select-menu"
        role="listbox">
        <template x-for="option in options" :key="option.value + option.text">
            <li role="option"
                class="sc-select-option"
                :class="{
                    'is-active': option.selected,
                    'is-disabled': option.disabled,
                }"
                @click="choose(option)"
                x-text="option.text"></li>
        </template>
    </ul>
</div>
