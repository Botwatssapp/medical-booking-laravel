<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-icons')
    <title>SantéConnect — @hasSection('title')@yield('title')@else@yield('page-title', 'Espace')@endif</title>
    @hasSection('meta')
        @yield('meta')
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#f5f7fb] text-[#0b1220] font-sans min-h-screen" x-data="scDemo()">
    <a href="#main-content" class="sc-skip-link">Aller au contenu</a>

    @unless(request()->routeIs('admin.*', 'doctor.*', 'landing'))
    <nav class="bg-white/90 backdrop-blur border-b border-[#e6ebf5] sticky top-0 z-50" x-data="{ open: false, logoutOpen: false }" aria-label="Navigation principale">
        <div class="sc-page-wide h-16 sm:h-[4.5rem] flex items-center justify-between gap-3 sm:gap-4">
            <a href="{{ auth()->check() ? auth()->user()->dashboardPath() : route('landing') }}"
               class="flex items-center gap-2 text-lg sm:text-xl font-bold text-[#0b1220] shrink-0 focus-visible:ring-2 focus-visible:ring-[#2563eb]/40 rounded-lg">
                <span class="w-9 h-9 rounded-xl bg-[#0b1220] text-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">favorite</span>
                </span>
                SantéConnect
            </a>

            @auth
                @if(Auth::user()->isPatient())
                    @php
                        $patientLink = fn (string $pattern) => request()->routeIs($pattern) ? 'sc-nav-link-active' : 'sc-nav-link';
                    @endphp
                    <div class="hidden md:flex items-center justify-center gap-1 flex-1 min-w-0">
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
                        <a href="{{ route('patient.profile.edit') }}"
                           class="{{ $patientLink('patient.profile.*') }}"
                           @if(request()->routeIs('patient.profile.*')) aria-current="page" @endif>
                            Mon profil
                        </a>
                    </div>
                @endif
            @else
                <div class="hidden md:flex items-center gap-1 flex-1">
                    <a href="{{ route('landing') }}" class="{{ request()->routeIs('landing') ? 'sc-nav-link-active' : 'sc-nav-link' }}">Accueil</a>
                    <a href="{{ route('landing') }}#specialites" class="sc-nav-link">Spécialités</a>
                    <a href="{{ route('login') }}" class="sc-nav-link">Annuaire</a>
                </div>
            @endauth

            <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0">
                @auth
                    @if(Auth::user()->isPatient())
                        @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                        <a href="{{ route('patient.notifications.index') }}"
                           class="relative w-10 h-10 flex items-center justify-center rounded-xl border border-[#e6ebf5] text-[#5b6573] hover:bg-[#eef2ff] hover:text-[#003f87] transition-colors"
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
                                class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-[#e6ebf5] text-[#5b6573]"
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
                        <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-[#e6ebf5] group-hover:border-[#2563eb] transition-colors shrink-0">
                            @if(Auth::user()->profile_image_url)
                                <img src="{{ Auth::user()->profile_image_url }}"
                                     alt="Photo de profil de {{ Auth::user()->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-[#0b1220] flex items-center justify-center text-white text-sm font-bold" aria-hidden="true">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <span class="hidden sm:inline text-sm text-[#5b6573] group-hover:text-[#003f87] transition-colors truncate max-w-[140px]">
                            {{ Auth::user()->name }}
                        </span>
                    </a>

                    <button type="button" class="hidden md:inline-flex sc-btn-primary !min-h-10 !px-3 sm:!px-4 !py-2 text-sm" @click="logoutOpen = true">
                        Déconnexion
                    </button>
                    <x-ui.modal name="logoutOpen" title="Confirmer la déconnexion">
                        <p class="text-sm text-[#5b6573] mb-5">Vous allez quitter votre espace SantéConnect. Cette action ferme uniquement la session en cours.</p>
                        <div class="flex flex-col sm:flex-row gap-2 justify-end">
                            <button type="button" class="sc-btn-secondary" @click="logoutOpen = false">Rester connecté</button>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="sc-btn-primary w-full">Déconnexion</button>
                            </form>
                        </div>
                    </x-ui.modal>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-[#003f87] hover:underline">Connexion</a>
                    <a href="{{ route('register') }}" class="sc-btn-primary !min-h-10 !px-4 !py-2 text-sm">Inscription</a>
                @endauth
            </div>
        </div>

        @auth
            @if(Auth::user()->isPatient())
                <div id="patient-mobile-nav"
                     class="md:hidden border-t border-[#e6ebf5] px-4 sm:px-6 py-3 space-y-1 bg-white"
                     x-show="open"
                     x-cloak
                     x-transition
                     @keydown.escape.window="open = false">
                    <a href="{{ route('patient.dashboard') }}" class="block {{ request()->routeIs('patient.dashboard') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.dashboard')) aria-current="page" @endif>Tableau de bord</a>
                    <a href="{{ route('patient.doctors.index') }}" class="block {{ request()->routeIs('patient.doctors.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.doctors.*')) aria-current="page" @endif>Trouver un médecin</a>
                    <a href="{{ route('patient.appointments.index') }}" class="block {{ request()->routeIs('patient.appointments.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.appointments.*')) aria-current="page" @endif>Mes rendez-vous</a>
                    <a href="{{ route('patient.notifications.index') }}" class="block {{ request()->routeIs('patient.notifications.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.notifications.*')) aria-current="page" @endif>Notifications</a>
                    <a href="{{ route('patient.profile.edit') }}" class="block {{ request()->routeIs('patient.profile.*') ? 'sc-nav-link-active' : 'sc-nav-link' }}" @if(request()->routeIs('patient.profile.*')) aria-current="page" @endif>Mon profil</a>
                    <button type="button" class="block sc-nav-link w-full text-left" @click="logoutOpen = true">Déconnexion</button>
                </div>
            @endif
        @endauth
    </nav>
    @endunless

    <main id="main-content" class="min-w-0 overflow-x-clip">
        @hasSection('suppress-global-flash')
        @else
            @include('components.ui.flash')
        @endif
        @yield('content')
    </main>

    @unless(request()->routeIs('doctor.*', 'admin.*', 'landing'))
        <footer class="bg-white border-t border-[#e6ebf5] py-10 mt-12">
            <div class="sc-page-wide grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-sm">
                <div>
                    <p class="font-bold text-[#0b1220]">SantéConnect</p>
                    <p class="text-[#5b6573] mt-2">Plateforme de prise de rendez-vous médicaux en ligne.</p>
                </div>
                <div>
                    <p class="font-semibold text-[#0b1220] mb-2">Patient</p>
                    <a href="{{ route('login') }}" class="block text-[#5b6573] hover:text-[#003f87]">Connexion</a>
                    <a href="{{ route('register') }}" class="block text-[#5b6573] hover:text-[#003f87]">Créer un compte</a>
                </div>
                <div>
                    <p class="font-semibold text-[#0b1220] mb-2">Découvrir</p>
                    <a href="{{ route('landing') }}" class="block text-[#5b6573] hover:text-[#003f87]">Accueil</a>
                    <a href="{{ route('landing') }}#specialites" class="block text-[#5b6573] hover:text-[#003f87]">Spécialités</a>
                </div>
                <div>
                    <p class="font-semibold text-[#0b1220] mb-2">Urgence</p>
                    <p class="text-[#5b6573]">En cas d’urgence vitale, appelez le 15 ou le 112. SantéConnect ne remplace pas un service d’urgence.</p>
                </div>
            </div>
            <p class="mt-8 text-center text-xs text-[#5b6573]">© {{ now()->year }} SantéConnect. Tous droits réservés.</p>
        </footer>
    @endunless
    @include('partials.demo-toast')
</body>
</html>
