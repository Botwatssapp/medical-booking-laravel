<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @include('partials.head-icons')
        <title>SantéConnect — {{ $title ?? 'Espace sécurisé' }}</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans text-[#0b1220] antialiased bg-[#f5f7fb]" x-data="scDemo()">
        <a href="#guest-content" class="sc-skip-link">Aller au contenu</a>
        @include('partials.auth-header')

        <div class="min-h-[calc(100vh-4rem)] grid grid-cols-1 lg:grid-cols-2">
            <aside class="relative hidden lg:flex flex-col justify-between p-10 xl:p-14 bg-gradient-to-br from-[#0b1220] via-[#12315f] to-[#2563eb] text-white">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/60">Espace médical sécurisé</p>
                    <h2 class="mt-4 text-3xl xl:text-4xl font-extrabold leading-tight">
                        Protection de vos données et de vos rendez-vous.
                    </h2>
                    <p class="mt-4 text-white/70 max-w-md text-sm leading-relaxed">
                        Réinitialisation par e-mail Laravel, session chiffrée côté serveur, et aucun secret affiché dans l’interface.
                    </p>
                </div>
                <div class="space-y-3">
                    @foreach([
                        ['icon' => 'link', 'title' => 'Lien à usage unique', 'text' => 'Le lien de réinitialisation expire automatiquement.'],
                        ['icon' => 'verified_user', 'title' => 'Compte existant uniquement', 'text' => 'Un e-mail n’est envoyé que si le compte existe.'],
                        ['icon' => 'support_agent', 'title' => 'Assistance', 'text' => 'En cas de souci, reconnectez-vous ou créez un compte.'],
                    ] as $item)
                        <div class="rounded-2xl bg-white/10 border border-white/10 p-4">
                            <p class="font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">{{ $item['icon'] }}</span>
                                {{ $item['title'] }}
                            </p>
                            <p class="text-sm text-white/70 mt-1">{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </aside>

            <main id="guest-content" class="flex items-center justify-center px-4 sm:px-6 lg:px-8 py-10">
                <div class="w-full max-w-md sc-card p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
        @include('partials.demo-toast')
    </body>
</html>
