@include('errors.layout', [
    'code' => '422',
    'title' => 'Données invalides',
    'message' => 'Le formulaire contient des informations incorrectes. Corrigez-les puis renvoyez-le.',
    'primaryHref' => url()->previous() ?: url('/'),
    'primaryLabel' => 'Revenir au formulaire',
])
