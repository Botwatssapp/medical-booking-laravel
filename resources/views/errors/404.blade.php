@include('errors.layout', [
    'code' => '404',
    'title' => 'Page introuvable',
    'message' => 'Cette adresse n’existe pas ou n’est plus disponible.',
    'primaryHref' => route('landing'),
    'primaryLabel' => 'Voir l’accueil',
])
