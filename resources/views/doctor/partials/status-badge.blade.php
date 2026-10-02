@include('components.ui.status-badge', [
    'appointment' => $appointment ?? null,
    'status' => $status ?? null,
    'tone' => 'default',
])
