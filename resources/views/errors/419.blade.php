@include('errors.layout', [
    'code' => '419',
    'title' => 'Session expirée',
    'message' => 'Le formulaire n’est plus valide. Rechargez la page, puis renvoyez-le.',
    'primaryHref' => url()->previous() ?: url('/'),
    'primaryLabel' => 'Revenir en arrière',
])
