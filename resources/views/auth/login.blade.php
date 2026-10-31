@php
    $doctorCount ??= \App\Models\Doctor::query()->count();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-icons')
    <title>SantéConnect — Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#faf8ff] font-sans text-[#131b2e] min-h-screen flex flex-col justify-between selection:bg-[#dbe1ff] selection:text-[#00174b]"
      x-data="scDemo()">
    <a href="#login-form" class="sc-skip-link">Aller au contenu</a>

    <header class="fixed top-0 w-full z-50 bg-[#faf8ff]/85 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.03)]">
        <div class="h-16 sm:h-20 sc-page-wide flex items-center justify-between gap-3">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 min-w-0">
                <span class="w-8 h-8 rounded-lg bg-[#2563eb] text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">favorite</span>
                </span>
                <span class="flex flex-col min-w-0">
                    <span class="font-display text-[16px] leading-6 font-bold tracking-tight truncate">SantéConnect</span>
                    <span class="text-[11px] text-[#434655] leading-none">Espace médical</span>
                </span>
            </a>
            <div class="flex items-center gap-4 lg:gap-6">
                <div class="hidden sm:flex items-center gap-1 bg-[#ffdad6] text-[#93000a] px-3 py-1.5 rounded-full">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">emergency</span>
                    <span class="text-[12px] uppercase tracking-wider font-semibold">Urgence : 15 · 112</span>
                </div>
                <nav class="flex md:hidden items-center gap-2" aria-label="Navigation authentification mobile">
                    <a href="{{ route('register') }}" class="text-xs font-semibold text-[#004ac6]">Inscription</a>
                    <a href="{{ route('landing') }}" class="text-xs font-semibold text-[#434655]">Accueil</a>
                </nav>
                <nav class="hidden md:flex items-center gap-4" aria-label="Navigation authentification">
                    <a href="{{ route('login') }}" class="text-[14px] font-semibold text-[#004ac6]" aria-current="page">Connexion</a>
                    <a href="{{ route('register') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Créer un compte</a>
                    <a href="{{ route('landing') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Retour au site</a>
                </nav>
                <span class="hidden sm:flex w-8 h-8 rounded-full bg-[#004ac6] items-center justify-center" aria-hidden="true">
                    <span class="material-symbols-outlined text-white text-[18px]">person</span>
                </span>
            </div>
        </div>
    </header>

    <main class="w-full pt-16 sm:pt-20 flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8" x-data="{ role: 'patient', showPw: false }">
        <div class="w-full max-w-[1240px] my-auto py-6 sm:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                <section class="lg:col-span-5 flex flex-col justify-between rounded-xl bg-[#f2f3ff] p-6 sm:p-8 relative overflow-hidden shadow-sm">
                    <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-[#004ac6]/10 blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-[#86f2e4]/30 blur-2xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="inline-flex items-center gap-2 self-start px-3 py-1 rounded-full bg-white text-[#006a61] shadow-sm">
                            <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">verified</span>
                            <span class="text-[12px] uppercase tracking-wide font-semibold">Espace médical</span>
                        </div>
                        <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight mt-1">
                            Accédez à vos rendez-vous et à vos médecins en un seul endroit.
                        </h1>
                        <p class="text-[15px] leading-6 text-[#434655] max-w-sm">
                            Connectez un compte patient, médecin ou administrateur déjà créé. Aucune statistique marketing n’est inventée ici.
                        </p>
                        <div class="relative w-full h-36 rounded-lg overflow-hidden my-1 shadow-sm">
                            <img src="{{ asset('images/hero-doctor.jpg') }}" alt="Consultation médicale en cabinet" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#283044]/80 via-[#283044]/20 to-transparent flex items-end p-3">
                                <div class="flex items-center gap-2 text-white">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#6ffbbe] animate-pulse"></span>
                                    <span class="text-[11px] font-semibold tracking-wider uppercase">{{ $doctorCount }} médecin{{ $doctorCount > 1 ? 's' : '' }} validé{{ $doctorCount > 1 ? 's' : '' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-start gap-3 p-3 rounded-lg bg-white/80 shadow-sm">
                                <div class="w-9 h-9 rounded-lg bg-[#e2e7ff] text-[#004ac6] flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_today</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-display text-[16px] leading-6 font-semibold">Prise de rendez-vous</p>
                                    <p class="text-[13px] leading-5 text-[#434655]">Uniquement sur des créneaux encore libres. La demande reste en attente.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-lg bg-white/80 shadow-sm">
                                <div class="w-9 h-9 rounded-lg bg-[#e2e7ff] text-[#006a61] flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">shield_lock</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-display text-[16px] leading-6 font-semibold">Session protégée</p>
                                    <p class="text-[13px] leading-5 text-[#434655]">Connexion par e-mail et mot de passe. Aucun coffre médical ni téléconsultation ici.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-lg bg-white/80 shadow-sm">
                                <div class="w-9 h-9 rounded-lg bg-[#e2e7ff] text-[#006242] flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">stethoscope</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-display text-[16px] leading-6 font-semibold">Trois espaces</p>
                                    <p class="text-[13px] leading-5 text-[#434655]">Patient, médecin ou administrateur selon le compte réellement créé.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative z-10 mt-4 pt-3">
                        <div class="p-4 rounded-lg bg-white shadow-sm">
                            <p class="text-[13px] leading-5 text-[#131b2e]">Aucun avis patient n’est encore enregistré.</p>
                            <p class="text-[11px] text-[#434655] mt-1">SantéConnect n’affiche pas de témoignage, de note ou de ville fictive.</p>
                        </div>
                    </div>
                </section>

                <section class="lg:col-span-7 flex flex-col justify-center">
                    <div class="rounded-xl bg-white p-6 sm:p-8 shadow-md flex flex-col">
                        <div class="flex items-center p-1.5 rounded-lg bg-[#f2f3ff] mb-4">
                            <button type="button"
                                    class="flex-1 py-2 px-3 rounded-lg text-[14px] font-semibold transition-all flex items-center justify-center gap-2"
                                    :class="role === 'patient' ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655] hover:text-[#131b2e]'"
                                    @click="role = 'patient'">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person</span>
                                Espace Patient
                            </button>
                            <button type="button"
                                    class="flex-1 py-2 px-3 rounded-lg text-[14px] font-semibold transition-all flex items-center justify-center gap-2"
                                    :class="role === 'pro' ? 'bg-white text-[#004ac6] shadow-sm' : 'text-[#434655] hover:text-[#131b2e]'"
                                    @click="role = 'pro'">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">medical_services</span>
                                Médecin / Clinique
                            </button>
                        </div>

                        <div class="flex flex-col mb-4">
                            <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold">Bon retour parmi nous</h2>
                            <p class="text-[15px] leading-6 text-[#434655] mt-1" x-show="role === 'patient'">
                                Saisissez vos identifiants pour accéder à votre espace.
                            </p>
                            <p class="text-[15px] leading-6 text-[#434655] mt-1" x-show="role === 'pro'" x-cloak>
                                Même formulaire : un compte médecin ou administrateur ouvre son propre espace.
                            </p>
                        </div>

                        @if($errors->any())
                            <div class="border border-red-200 bg-red-50 rounded-xl px-4 py-3 mb-4" role="alert">
                                @foreach($errors->all() as $error)
                                    <p class="text-sm text-red-700">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="border border-green-200 bg-green-50 rounded-xl px-4 py-3 mb-4" role="status">
                                <p class="text-sm text-green-800">{{ session('status') }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                            <button type="button" class="h-11 px-4 rounded-lg bg-[#f2f3ff] hover:bg-[#eaedff] flex items-center justify-center gap-3 text-[14px] font-semibold shadow-sm"
                                    @click="simulateOauth('Google')">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"></path>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"></path>
                                </svg>
                                Continuer avec Google
                            </button>
                            <button type="button" class="h-11 px-4 rounded-lg bg-[#f2f3ff] hover:bg-[#eaedff] flex items-center justify-center gap-3 text-[14px] font-semibold shadow-sm"
                                    @click="simulateOauth('Apple')">
                                <svg class="w-4 h-4 shrink-0 fill-current" viewBox="0 0 170 170" aria-hidden="true">
                                    <path d="M150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.19-2.12-9.97-3.17-14.34-3.17-4.58 0-9.49 1.05-14.75 3.17-5.26 2.13-9.5 3.24-12.74 3.35-4.35.13-9.16-1.9-14.42-6.08-3.69-3.04-7.67-7.85-11.96-14.42-7.55-11.58-13.43-24.87-17.65-39.87-4.21-15-6.33-28.9-6.33-41.68 0-16.71 4.38-30.73 13.15-42.06 8.77-11.33 19.8-17.15 33.1-17.47 4.9 0 10.3 1.25 16.2 3.75 5.9 2.5 9.77 3.86 11.61 4.08 2.34-.33 6.38-1.78 12.12-4.35 5.74-2.57 10.74-3.75 15-3.54 11.75.87 21.6 5.34 29.56 13.42-10.45 6.31-15.56 15.02-15.34 26.13.22 8.71 3.53 16.11 9.92 22.21 6.39 6.09 14.15 9.79 23.29 11.09-2.07 6.42-4.57 12.63-7.51 18.63zM119.22 31.84c0-7.72 2.77-15 8.32-21.84 5.55-6.84 12.4-10.94 20.55-12.3 0 1.2-.11 2.51-.33 3.92-.87 7.07-3.8 14.04-8.8 20.91-5 6.87-11.43 11-19.29 12.4-.22-1.09-.45-2.12-.45-3.09z"></path>
                                </svg>
                                Continuer avec Apple
                            </button>
                        </div>

                        <div class="relative flex items-center justify-center mb-4">
                            <div class="w-full h-px bg-[#e2e7ff]"></div>
                            <span class="absolute px-3 bg-white text-[12px] font-semibold text-[#434655] uppercase tracking-wider">Ou par e-mail</span>
                        </div>

                        <form id="login-form" action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
                            @csrf
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[14px] font-semibold flex justify-between items-center" for="email">
                                    <span>Adresse e-mail <span class="text-[#ba1a1a]">*</span></span>
                                    <span class="text-[11px] text-[#434655] font-normal">Requis</span>
                                </label>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-[#434655] text-[20px] pointer-events-none" aria-hidden="true">mail</span>
                                    <input class="w-full h-11 pl-11 pr-4 rounded-lg bg-[#f2f3ff] focus:bg-white text-[15px] shadow-sm outline-none focus:ring-2 focus:ring-[#004ac6] placeholder:text-[#737686]"
                                           id="email" name="email" type="email" value="{{ old('email') }}"
                                           placeholder="nom@exemple.com" required autofocus autocomplete="username">
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[14px] font-semibold" for="pw">Mot de passe <span class="text-[#ba1a1a]">*</span></label>
                                    @if(Route::has('password.request'))
                                        <a class="text-[12px] font-semibold text-[#004ac6] hover:underline" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                                    @endif
                                </div>
                                <div class="relative flex items-center">
                                    <span class="material-symbols-outlined absolute left-3.5 text-[#434655] text-[20px] pointer-events-none" aria-hidden="true">lock</span>
                                    <input class="w-full h-11 pl-11 pr-11 rounded-lg bg-[#f2f3ff] focus:bg-white text-[15px] shadow-sm outline-none focus:ring-2 focus:ring-[#004ac6] placeholder:text-[#737686]"
                                           id="pw" name="password" :type="showPw ? 'text' : 'password'"
                                           required autocomplete="current-password" placeholder="••••••••">
                                    <button type="button" class="absolute right-3.5 text-[#434655] hover:text-[#131b2e]"
                                            @click="showPw = !showPw"
                                            :aria-label="showPw ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                                        <span class="material-symbols-outlined text-[20px]" x-text="showPw ? 'visibility_off' : 'visibility'" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded text-[#004ac6] accent-[#004ac6]">
                                    <span class="text-[13px] text-[#434655]">Se souvenir de moi</span>
                                </label>
                                <div class="hidden sm:flex items-center gap-1 text-[#006a61] text-[11px] font-semibold">
                                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">vpn_key</span>
                                    Session protégée
                                </div>
                            </div>
                            <button type="submit" class="w-full h-12 rounded-lg bg-[#004ac6] hover:bg-[#003ea8] text-white font-display text-[16px] font-semibold flex items-center justify-center gap-2 shadow-md">
                                <span>Se connecter</span>
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                            </button>
                        </form>

                        <div class="mt-4 p-3 rounded-lg bg-[#f2f3ff] flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#89f5e7]/50 text-[#005049] flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">verified_user</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold">Connexion par e-mail et mot de passe</p>
                                <p class="text-[13px] text-[#434655]">La double authentification n’est pas branchée. Aucune certification externe n’est affichée ici.</p>
                            </div>
                        </div>

                        <p class="mt-4 pt-1 text-center text-[13px] text-[#434655]">
                            Vous découvrez SantéConnect ?
                            <a class="font-semibold text-[#004ac6] hover:underline ml-1" href="{{ route('register') }}">Créer un compte patient</a>
                        </p>

                        <div class="mt-3 pt-3 bg-[#e2e7ff]/40 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 p-4 rounded-b-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#006a61] text-[20px]" aria-hidden="true">stethoscope</span>
                                <span class="text-[12px] font-semibold">Vous êtes médecin ou administrateur ?</span>
                            </div>
                            <button type="button" class="text-[14px] text-[#004ac6] font-semibold flex items-center gap-1"
                                    @click="role = 'pro'">
                                Même formulaire
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <footer class="w-full bg-[#f2f3ff] shadow-[0_-1px_6px_rgba(0,0,0,0.02)]">
        <div class="max-w-[1360px] mx-auto px-4 lg:px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-[12px] text-[#434655]">
            <p class="flex items-center gap-1.5 text-[#006a61] font-semibold">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">health_and_safety</span>
                Urgences : 15 / 112
            </p>
            <p>© {{ now()->year }} SantéConnect. Tous droits réservés.</p>
        </div>
    </footer>
    @include('partials.demo-toast')
</body>
</html>
