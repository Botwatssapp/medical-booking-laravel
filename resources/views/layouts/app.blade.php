<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SantéConnect — @hasSection('title')@yield('title')@else@yield('page-title', 'Espace')@endif</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#f8f9ff] text-[#0d1c2f] font-sans min-h-screen">
    <a href="#main-content" class="sc-skip-link">Aller au contenu</a>

    <nav class="bg-white border-b border-[#c2c6d4] sticky top-0 z-50" x-data="{ open: false }" aria-label="Navigation principale">
        <div class="flex justify-between items-center px-4 sm:px-8 h-16 sm:h-20 max-w-[1440px] mx-auto gap-3">
            <a href="{{ auth()->check() ? auth()->user()->dashboardPath() : route('home') }}"
               class="text-lg sm:text-2xl font-bold text-[#003f87] shrink-0 focus-visible:ring-2 focus-visible:ring-[#003f87]/40 rounded-lg">
                SantéConnect
            </a>

            @auth
                @if(Auth::user()->isPatient())
                    @php
                        $patientLink = fn (string $pattern) => request()->routeIs($pattern) ? 'sc-nav-link-active' : 'sc-nav-link';
                    @endphp
                    <div class="hidden md:flex items-center gap-1 flex-1 min-w-0">
                        <a href="{{ route('patient.dashboard') }}"
                           class="{{ $patientLink('patient.dashboard') }}"
                           @if(request()->routeIs('patient.dashboard')) aria-current="page" @endif>
                            Tableau de bord
                        </a>
                        <a href="{{ route('patient.doctors.index') }}"
                           class="{{ $patientLink('patient.doctors.*') }}"
                           @if(request()->routeIs('patient.doctors.*')) aria-current="page" @endif>
                            Trouver un médecin
                        </a>
                        <a href="{{ route('patient.appointments.index') }}"
                           class="{{ $patientLink('patient.appointments.*') }}"
                           @if(request()->routeIs('patient.appointments.*')) aria-current="page" @endif>
                            Mes rendez-vous
                        </a>
                    </div>
                @endif
            @endauth

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    @if(Auth::user()->isPatient())
                        @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                        <a href="{{ route('patient.notifications.index') }}"
                           class="relative w-10 h-10 flex items-center justify-center rounded-xl border border-[#c2c6d4] text-[#526069] hover:bg-[#eff4ff] hover:text-[#003f87] transition-colors"
                           aria-label="Notifications{{ $unreadCount > 0 ? ' ('.$unreadCount.' non lues)' : '' }}"
                           @if(request()->routeIs('patient.notifications.*')) aria-current="page" @endif>
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">notifications</span>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1
                                             bg-red-600 text-white text-[10px] font-bold rounded-full
                                             flex items-center justify-center leading-none"
                                      aria-hidden="true">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </a>

                        <button type="button"
                                class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-[#c2c6d4] text-[#526069]"
                                @click="open = !open"
                                :aria-expanded="open.toString()"
                                aria-controls="patient-mobile-nav"
                                :aria-label="open ? 'Fermer le menu' : 'Ouvrir le menu'">
                            <span class="material-symbols-outlined" aria-hidden="true" x-text="open ? 'close' : 'menu'">menu</span>
                        </button>
                    @endif

                    @php
                        $profileRoute = match(Auth::user()->role) {
                            'patient' => 'patient.profile.edit',
                            'doctor'  => 'doctor.profile.edit',
                            'admin'   => 'admin.profile.edit',
                            default   => null,
                        };
                    @endphp
                    <a href="{{ $profileRoute ? route($profileRoute) : '#' }}"
                       class="flex items-center gap-2 group min-w-0"
                       aria-label="Mon profil"
                       @if($profileRoute && request()->routeIs(str_replace('.edit', '.*', $profileRoute))) aria-current="page" @endif>
                        <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-[#c2c6d4] group-hover:border-[#003f87] transition-colors shrink-0">
                            @if(Auth::user()->profile_image_url)
                                <img src="{{ Auth::user()->profile_image_url }}"
                                     alt="Photo de profil de {{ Auth::user()->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-[#003f87] flex items-center justify-center text-white text-sm font-bold" aria-hidden="true">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <span class="hidden sm:inline text-sm text-[#526069] group-hover:text-[#003f87] transition-colors truncate max-w-[140px]">
                            {{ Auth::user()->name }}
                        </span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="sc-btn-primary !min-h-10 !px-3 sm:!px-4 !py-2 text-sm">
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-[#003f87] hover:underline">Connexion</a>
                    <a href="{{ route('register') }}" class="sc-btn-primary !min-h-10 !px-4 !py-2 text-sm">Inscription</a>
                @endauth
            </div>
        </div>

        @auth
            @if(Auth::user()->isPatient())
                <div id="patient-mobile-nav"
                     class="md:hidden border-t border-[#e0e7ff] px-4 py-3 space-y-1 bg-white"
                     x-show="open"
                     x-cloak
                     x-transition
                     @keydown.escape.window="open = false">
                    <a href="{{ route('patient.dashboard') }}" class="block {{ request()->routeIs('patient.dashboard') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.dashboard')) aria-current="page" @endif>Tableau de bord</a>
                    <a href="{{ route('patient.doctors.index') }}" class="block {{ request()->routeIs('patient.doctors.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.doctors.*')) aria-current="page" @endif>Trouver un médecin</a>
                    <a href="{{ route('patient.appointments.index') }}" class="block {{ request()->routeIs('patient.appointments.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.appointments.*')) aria-current="page" @endif>Mes rendez-vous</a>
                    <a href="{{ route('patient.notifications.index') }}" class="block {{ request()->routeIs('patient.notifications.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.notifications.*')) aria-current="page" @endif>Notifications</a>
                    <a href="{{ route('patient.profile.edit') }}" class="block {{ request()->routeIs('patient.profile.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.profile.*')) aria-current="page" @endif>Mon profil</a>
                </div>
            @endif
        @endauth
    </nav>

    <main id="main-content">
        @hasSection('suppress-global-flash')
        @else
            @include('components.ui.flash')
        @endif
        @yield('content')
    </main>

    @unless(request()->routeIs('doctor.*', 'admin.*'))
        <footer class="bg-[#d5e3fd] border-t border-[#c2c6d4] py-8 mt-12">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-8 text-center">
                <p class="text-sm text-[#424752]">© {{ now()->year }} SantéConnect. Tous droits réservés.</p>
            </div>
        </footer>
    @endunless
</body>
</html>
