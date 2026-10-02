<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>SantéConnect — {{ $title ?? 'Espace sécurisé' }}</title>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans text-[#0d1c2f] antialiased bg-[#f8f9ff]">
        <a href="#guest-content" class="sc-skip-link">Aller au contenu</a>
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-8 px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 mb-6">
                <span class="w-10 h-10 bg-[#003f87] rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-xl" aria-hidden="true">favorite</span>
                </span>
                <span class="text-xl font-bold text-[#003f87]">SantéConnect</span>
            </a>

            <div id="guest-content" class="w-full sm:max-w-md sc-card px-6 py-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
