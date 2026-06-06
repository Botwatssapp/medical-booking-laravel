<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SantéConnect — @yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8f9ff] text-[#0d1c2f] font-sans">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-[#c2c6d4] sticky top-0 z-50">
        <div class="flex justify-between items-center px-8 h-20 max-w-[1440px] mx-auto">
            <a href="/" class="text-2xl font-bold text-[#003f87]">SantéConnect</a>
            <div class="flex items-center gap-4">
                @auth
                    <span class="text-sm text-[#526069]">{{ Auth::user()->name }}</span>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button class="px-4 py-2 bg-[#003f87] text-white rounded-lg text-sm hover:opacity-90">
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="/login" class="text-[#003f87] hover:underline">Connexion</a>
                    <a href="/register" class="px-4 py-2 bg-[#003f87] text-white rounded-lg text-sm hover:opacity-90">Inscription</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-[1440px] mx-auto px-8 mt-4">
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-[1440px] mx-auto px-8 mt-4">
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-xl text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-[#d5e3fd] border-t border-[#c2c6d4] py-8 mt-12">
        <div class="max-w-[1440px] mx-auto px-8 text-center">
            <p class="text-sm text-[#424752]">© 2024 SantéConnect. Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>
