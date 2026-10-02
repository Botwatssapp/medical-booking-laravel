@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-800 bg-green-50 border border-green-200 rounded-xl px-4 py-3']) }} role="status">
        {{ $status }}
    </div>
@endif
