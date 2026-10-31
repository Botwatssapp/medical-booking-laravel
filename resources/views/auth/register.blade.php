<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-icons')
    <title>SantéConnect — Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#faf8ff] font-sans text-[#131b2e] min-h-screen flex flex-col justify-between selection:bg-[#dbe1ff] selection:text-[#00174b]"
      x-data="scDemo()">
    <a href="#register-form" class="sc-skip-link">Aller au contenu</a>

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
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-[#004ac6]">Connexion</a>
                    <a href="{{ route('landing') }}" class="text-xs font-semibold text-[#434655]">Accueil</a>
                </nav>
                <nav class="hidden md:flex items-center gap-4" aria-label="Navigation authentification">
                    <a href="{{ route('login') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Connexion</a>
                    <a href="{{ route('register') }}" class="text-[14px] font-semibold text-[#004ac6]" aria-current="page">Créer un compte</a>
                    <a href="{{ route('landing') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Retour au site</a>
                </nav>
                <span class="hidden sm:flex w-8 h-8 rounded-full bg-[#004ac6] items-center justify-center" aria-hidden="true">
                    <span class="material-symbols-outlined text-white text-[18px]">person</span>
                </span>
            </div>
        </div>
    </header>

    <main class="w-full pt-16 sm:pt-20 flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8" x-data="{ role: '{{ old('role', 'patient') }}', showPw: false, password: '' }">
        <div class="w-full max-w-[1360px] mx-auto py-6 lg:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                <section class="lg:col-span-5 flex flex-col justify-between p-6 lg:p-8 rounded-xl bg-gradient-to-br from-[#004ac6] via-[#2563eb] to-[#006a61] text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-12 bottom-12 w-60 h-60 rounded-full bg-[#89f5e7]/20 blur-2xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col gap-6">
                        <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md self-start shadow-sm">
                            <span class="material-symbols-outlined text-[18px] text-[#6ffbbe]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">verified</span>
                            <span class="text-[12px] tracking-wide font-semibold uppercase">{{ $doctorCount }} médecin{{ $doctorCount > 1 ? 's' : '' }} validé{{ $doctorCount > 1 ? 's' : '' }}</span>
                        </div>
                        <div>
                            <h1 class="font-display text-[32px] lg:text-[40px] leading-10 lg:leading-[48px] font-bold tracking-tight">
                                Créez votre compte pour réserver ou gérer des créneaux.
                            </h1>
                            <p class="text-[15px] leading-6 text-white/85 mt-2">
                                Un compte patient peut demander un rendez-vous. Un compte médecin attend la validation d’un administrateur.
                            </p>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start gap-4 p-3.5 rounded-lg bg-white/10">
                                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">person</span>
                                </div>
                                <div>
                                    <p class="font-display text-[16px] leading-6 font-semibold">Compte patient</p>
                                    <p class="text-[13px] leading-5 text-white/80 mt-0.5">Recherche de médecins validés et demandes sur créneaux libres.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 p-3.5 rounded-lg bg-white/10">
                                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">stethoscope</span>
                                </div>
                                <div>
                                    <p class="font-display text-[16px] leading-6 font-semibold">Compte médecin</p>
                                    <p class="text-[13px] leading-5 text-white/80 mt-0.5">L’agenda n’apparaît qu’après validation administrative. Aucun cabinet n’est créé ici.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 p-3.5 rounded-lg bg-white/10">
                                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">mail</span>
                                </div>
                                <div>
                                    <p class="font-display text-[16px] leading-6 font-semibold">E-mail uniquement</p>
                                    <p class="text-[13px] leading-5 text-white/80 mt-0.5">Aucun SMS, WhatsApp, mutuelle ou dossier familial n’est enregistré à l’inscription.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 p-3.5 rounded-lg bg-white/10">
                                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">event_available</span>
                                </div>
                                <div>
                                    <p class="font-display text-[16px] leading-6 font-semibold">Demande en attente</p>
                                    <p class="text-[13px] leading-5 text-white/80 mt-0.5">Un rendez-vous n’est pas confirmé instantanément. La téléconsultation n’est pas gérée.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative z-10 pt-6 mt-6 border-t border-white/15 text-[11px] text-white/80">
                        <p class="flex items-center gap-2 text-white">
                            <span class="material-symbols-outlined text-[#89f5e7] text-[20px]" aria-hidden="true">lock</span>
                            Session protégée côté serveur
                        </p>
                        <p class="mt-1">Aucune certification externe n’est affichée ici. Urgence vitale : 15 ou 112.</p>
                    </div>
                </section>

                <section class="lg:col-span-7 bg-white rounded-xl p-6 lg:p-8 shadow-lg flex flex-col">
                    <div class="flex items-center justify-between flex-wrap gap-3 pb-2">
                        <div class="inline-flex p-1 bg-[#f2f3ff] rounded-lg">
                            <button type="button" class="flex items-center gap-1.5 px-4 py-2 rounded-md text-[12px] font-semibold"
                                    :class="role === 'patient' ? 'bg-[#004ac6] text-white shadow-sm' : 'text-[#434655]'"
                                    @click="role = 'patient'">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person</span>
                                Compte Patient
                            </button>
                            <button type="button" class="flex items-center gap-1.5 px-4 py-2 rounded-md text-[12px] font-semibold"
                                    :class="role === 'doctor' ? 'bg-[#006a61] text-white shadow-sm' : 'text-[#434655]'"
                                    @click="role = 'doctor'">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">medical_services</span>
                                Médecin / Clinique
                            </button>
                        </div>
                        <div class="flex items-center gap-1.5 text-[#006a61] text-[12px] font-semibold bg-[#86f2e4]/30 px-3 py-1.5 rounded-full">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">info</span>
                            Champs réellement enregistrés
                        </div>
                    </div>

                    <div class="mt-4">
                        <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight" x-text="role === 'doctor' ? 'Créer votre compte médecin' : 'Créer votre compte patient'"></h2>
                        <p class="text-[15px] leading-6 text-[#434655] mt-1">
                            Les champs ci-dessous sont ceux réellement enregistrés : nom, e-mail, mot de passe et rôle.
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="border border-red-200 bg-red-50 rounded-xl px-4 py-3 mt-4" role="alert">
                            @foreach($errors->all() as $error)
                                <p class="text-sm text-red-700">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-6">
                        <button type="button" class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg bg-[#f2f3ff] hover:bg-[#eaedff] text-[14px] font-semibold shadow-sm"
                                @click="simulateOauth('Google')">
                            Continuer avec Google
                        </button>
                        <button type="button" class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg bg-[#f2f3ff] hover:bg-[#eaedff] text-[14px] font-semibold shadow-sm"
                                @click="simulateOauth('Apple')">
                            Continuer avec Apple
                        </button>
                    </div>

                    <div class="relative flex items-center justify-center my-4">
                        <div class="w-full h-px bg-[#c3c6d7]/40"></div>
                        <span class="absolute px-3 bg-white text-[11px] text-[#434655] uppercase tracking-wider">ou avec vos coordonnées</span>
                    </div>

                    <form id="register-form" action="{{ route('register') }}" method="POST" class="flex flex-col gap-6">
                        @csrf
                        <input type="hidden" name="role" value="{{ old('role', 'patient') }}" x-bind:value="role">

                        <div class="flex flex-col gap-3">
                            <div class="flex items-center gap-2 text-[#004ac6] text-[14px] font-semibold">
                                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-[#dbe1ff] text-[#00174b] text-[11px] font-bold">1</span>
                                Identité
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[12px] font-semibold" for="name">Nom complet <span class="text-[#ba1a1a]">*</span></label>
                                <input class="w-full h-11 px-3.5 bg-[#f2f3ff] focus:bg-white rounded-lg text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                       id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" placeholder="ex. Jean Dupont">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[12px] font-semibold" for="email">Adresse e-mail <span class="text-[#ba1a1a]">*</span></label>
                                <input class="w-full h-11 px-3.5 bg-[#f2f3ff] focus:bg-white rounded-lg text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                       id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nom@exemple.com">
                            </div>
                            <button type="button" class="h-11 px-3 rounded-lg bg-[#f2f3ff] text-[13px] text-[#434655] text-left"
                                    @click="showToast('Le téléphone n’est pas enregistré à l’inscription.')">
                                Téléphone : non géré — utilisez l’e-mail
                            </button>
                        </div>

                        <div class="flex flex-col gap-3">
                            <div class="flex items-center gap-2 text-[#004ac6] text-[14px] font-semibold">
                                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-[#dbe1ff] text-[#00174b] text-[11px] font-bold">2</span>
                                Sécurité du compte
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[12px] font-semibold" for="pw">Mot de passe <span class="text-[#ba1a1a]">*</span></label>
                                    <div class="relative flex items-center">
                                        <input class="w-full h-11 pl-3.5 pr-10 bg-[#f2f3ff] focus:bg-white rounded-lg text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                               id="pw" name="password" :type="showPw ? 'text' : 'password'" x-model="password"
                                               required autocomplete="new-password" placeholder="Minimum 8 caractères">
                                        <button type="button" class="absolute right-3 text-[#434655]" @click="showPw = !showPw"
                                                :aria-label="showPw ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                                            <span class="material-symbols-outlined text-[18px]" x-text="showPw ? 'visibility_off' : 'visibility'" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[12px] font-semibold" for="pw2">Confirmer le mot de passe <span class="text-[#ba1a1a]">*</span></label>
                                    <input class="w-full h-11 px-3.5 bg-[#f2f3ff] focus:bg-white rounded-lg text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                           id="pw2" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Confirmez à l’identique">
                                </div>
                            </div>
                            <div class="bg-[#f2f3ff]/60 p-3 rounded-lg">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-[#434655]">Longueur du mot de passe</span>
                                    <span class="font-semibold" :class="password.length >= 8 ? 'text-[#006a61]' : 'text-[#434655]'"
                                          x-text="password.length >= 8 ? 'Au moins 8 caractères' : 'Minimum 8 caractères requis'"></span>
                                </div>
                                <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full mt-2">
                                    <div class="h-full rounded-full" :class="password.length >= 2 ? 'bg-[#006a61]' : 'bg-[#c3c6d7]'"></div>
                                    <div class="h-full rounded-full" :class="password.length >= 4 ? 'bg-[#006a61]' : 'bg-[#c3c6d7]'"></div>
                                    <div class="h-full rounded-full" :class="password.length >= 6 ? 'bg-[#006a61]' : 'bg-[#c3c6d7]'"></div>
                                    <div class="h-full rounded-full" :class="password.length >= 8 ? 'bg-[#006a61]' : 'bg-[#c3c6d7]'"></div>
                                </div>
                                <p class="mt-2 text-[11px] text-[#434655] flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]" :class="password.length >= 8 ? 'text-[#006a61]' : 'text-[#737686]'" aria-hidden="true">check_circle</span>
                                    Le mot de passe doit contenir au moins 8 caractères.
                                </p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-lg bg-[#f2f3ff]/70">
                            <p class="text-[14px] font-semibold">Couverture maladie</p>
                            <p class="text-[13px] text-[#434655] mt-1">Aucune mutuelle n’est enregistrée ici. Le tiers-payant n’est pas géré.</p>
                            <button type="button" class="mt-2 w-full h-11 px-3.5 bg-white rounded-lg text-[15px] text-left text-[#737686] shadow-sm"
                                    @click="showToast('Aucune affiliation mutuelle n’est enregistrée.')">
                                Affiliation non disponible
                            </button>
                        </div>

                        <p class="text-[13px] text-[#434655]">En créant un compte, vous utilisez SantéConnect pour prendre ou gérer des rendez-vous. Aucune politique externe n’est affichée ici.</p>

                        <button type="submit" class="w-full h-12 bg-[#004ac6] hover:bg-[#003ea8] text-white rounded-lg font-display text-[16px] font-semibold shadow-md flex items-center justify-center gap-2">
                            <span>Créer mon compte</span>
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                        </button>

                        <p class="text-center text-[15px] text-[#434655]">
                            Vous possédez déjà un compte SantéConnect ?
                            <a class="text-[#004ac6] font-semibold hover:underline inline-flex items-center" href="{{ route('login') }}">
                                Se connecter
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                            </a>
                        </p>
                    </form>
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
