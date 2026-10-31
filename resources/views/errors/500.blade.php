@include('errors.layout', [
    'code' => '500',
    'title' => 'Erreur serveur',
    'message' => 'Une erreur interne s’est produite. Réessayez dans un instant.',
    'primaryHref' => url('/'),
    'primaryLabel' => 'Réessayer',
])
