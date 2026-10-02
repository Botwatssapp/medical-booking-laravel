@php
    $status = $status ?? ($appointment->status ?? '');
    $tone = $tone ?? 'default';
    $badge = match ($status) {
        'pending' => 'bg-yellow-100 text-yellow-900',
        'accepted' => 'bg-green-100 text-green-900',
        'rejected' => 'bg-red-100 text-red-900',
        'cancelled' => 'bg-gray-100 text-gray-800',
        'completed' => 'bg-blue-100 text-blue-900',
        'missed' => 'bg-orange-100 text-orange-900',
        default => 'bg-gray-100 text-gray-800',
    };
    $label = match ($status) {
        'pending' => 'En attente',
        'accepted' => $tone === 'admin' ? 'Accepté' : 'Confirmé',
        'rejected' => 'Refusé',
        'cancelled' => 'Annulé',
        'completed' => 'Terminé',
        'missed' => 'Non réalisé',
        default => ucfirst((string) $status),
    };
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badge }}"
      aria-label="Statut : {{ $label }}">
    {{ $label }}
</span>
