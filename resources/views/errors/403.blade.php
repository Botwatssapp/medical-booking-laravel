@php
    $dashboard = auth()->check() ? auth()->user()->dashboardPath() : route('login');
    $detail = trim((string) (($exception ?? null)?->getMessage() ?? ''));
    $safeDetail = $detail !== '' && ! str_contains(mb_strtolower($detail), 'exception')
        ? $detail
        : 'Vous n’avez pas l’autorisation d’ouvrir cette page.';
@endphp
@include('errors.layout', [
    'code' => '403',
    'title' => 'Accès refusé',
    'message' => $safeDetail,
    'primaryHref' => $dashboard,
    'primaryLabel' => auth()->check() ? 'Retour à mon espace' : 'Se connecter',
])
