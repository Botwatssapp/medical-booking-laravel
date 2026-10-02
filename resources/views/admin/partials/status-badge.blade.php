@include('components.ui.status-badge', [
    'appointment' => $appointment ?? null,
    'status' => $status ?? ($appointment->status ?? null),
    'tone' => 'admin',
])
