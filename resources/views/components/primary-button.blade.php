<button {{ $attributes->merge(['type' => 'submit', 'class' => 'sc-btn-primary']) }}>
    {{ $slot }}
</button>
