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
                    {{-- Cloche de notifications (patients uniquement) --}}
                    @if(Auth::user()->isPatient())
                        @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                        <a href="{{ route('patient.notifications.index') }}"
                           class="relative w-10 h-10 flex items-center justify-center rounded-xl border border-[#c2c6d4] text-[#526069] hover:bg-[#eff4ff] hover:text-[#003f87] transition-colors"
                           title="Notifications">
                            <span class="material-symbols-outlined text-xl">notifications</span>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1
                                             bg-red-500 text-white text-[10px] font-bold rounded-full
                                             flex items-center justify-center leading-none">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    {{-- Avatar + nom cliquable vers le profil --}}
                    @php
                        $profileRoute = match(Auth::user()->role) {
                            'patient' => 'patient.profile.edit',
                            'doctor'  => 'doctor.profile.edit',
                            'admin'   => 'admin.profile.edit',
                            default   => null,
                        };
                    @endphp
                    <a href="{{ $profileRoute ? route($profileRoute) : '#' }}"
                       class="flex items-center gap-2 group">
                        <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-[#c2c6d4] group-hover:border-[#003f87] transition-colors shrink-0">
                            @if(Auth::user()->profile_image_url)
                                <img src="{{ Auth::user()->profile_image_url }}"
                                     alt="{{ Auth::user()->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-[#003f87] flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <span class="text-sm text-[#526069] group-hover:text-[#003f87] transition-colors">
                            {{ Auth::user()->name }}
                        </span>
                    </a>

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
