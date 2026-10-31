<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-icons')
    <title>SantéConnect — Vérification en deux étapes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#faf8ff] font-sans text-[#131b2e] min-h-screen flex flex-col justify-between selection:bg-[#dbe1ff] selection:text-[#00174b]"
      x-data="scDemo()">
    <a href="#otp-0" class="sc-skip-link">Aller au contenu</a>

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
                <nav class="hidden md:flex items-center gap-4" aria-label="Navigation authentification">
                    <a href="{{ route('login') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Connexion</a>
                    <a href="{{ route('demo.two-factor') }}" class="text-[14px] font-semibold text-[#004ac6]" aria-current="page">Vérification 2FA</a>
                    <a href="{{ route('password.request') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Mot de passe oublié</a>
                    <a href="{{ route('landing') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Retour au site</a>
                </nav>
                <span class="w-8 h-8 rounded-full bg-[#004ac6] flex items-center justify-center" aria-hidden="true">
                    <span class="material-symbols-outlined text-white text-[18px]">person</span>
                </span>
            </div>
        </div>
    </header>

    <main class="w-full pt-16 sm:pt-20 flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[1240px] py-6 lg:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 rounded-xl shadow-xl overflow-hidden bg-white">
                <section class="lg:col-span-5 bg-gradient-to-br from-[#004ac6] via-[#2563eb] to-[#006a61] p-6 lg:p-8 text-white flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-[#89f5e7]/20 blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full bg-[#b4c5ff]/20 blur-3xl pointer-events-none"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md">
                            <span class="material-symbols-outlined text-[16px] text-[#89f5e7]" aria-hidden="true">shield_lock</span>
                            <span class="text-[12px] uppercase tracking-wider font-semibold">Aperçu 2FA</span>
                        </div>
                        <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight mt-2">
                            Accès hautement sécurisé à votre dossier médical.
                        </h1>
                        <p class="text-[15px] leading-6 text-white/85">
                            Cet écran reprend le design de vérification. Il n’active pas une double authentification. La connexion réelle reste e-mail et mot de passe.
                        </p>
                        <div class="space-y-3 pt-2">
                            <div class="flex items-start gap-3 p-3.5 rounded-lg bg-white/10">
                                <div class="w-8 h-8 rounded-md bg-white/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[#89f5e7] text-[20px]" aria-hidden="true">sms</span>
                                </div>
                                <div>
                                    <h2 class="font-display text-[16px] leading-6 font-semibold">Aucun SMS envoyé</h2>
                                    <p class="text-[13px] leading-5 text-white/80 mt-0.5">Aucun code n’est généré ni transmis à un téléphone.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3.5 rounded-lg bg-white/10">
                                <div class="w-8 h-8 rounded-md bg-white/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[#89f5e7] text-[20px]" aria-hidden="true">password</span>
                                </div>
                                <div>
                                    <h2 class="font-display text-[16px] leading-6 font-semibold">Aucune validation serveur</h2>
                                    <p class="text-[13px] leading-5 text-white/80 mt-0.5">Saisir six chiffres ici n’ouvre aucun compte et n’appelle aucun fournisseur.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3.5 rounded-lg bg-white/10">
                                <div class="w-8 h-8 rounded-md bg-white/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[#89f5e7] text-[20px]" aria-hidden="true">login</span>
                                </div>
                                <div>
                                    <h2 class="font-display text-[16px] leading-6 font-semibold">Connexion réelle</h2>
                                    <p class="text-[13px] leading-5 text-white/80 mt-0.5">Utilisez e-mail et mot de passe depuis la page de connexion.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative z-10 pt-6 space-y-3">
                        <div class="p-3 rounded-lg bg-white/15 flex items-center gap-3">
                            <span class="material-symbols-outlined text-[#89f5e7] text-[22px]" aria-hidden="true">devices</span>
                            <div class="min-w-0">
                                <p class="text-[11px] uppercase tracking-wide font-semibold text-[#89f5e7]">Aperçu visuel</p>
                                <p class="text-[13px] text-white/90">Aucun appareil ni lieu de connexion n’est inventé ici.</p>
                            </div>
                        </div>
                        <p class="text-[11px] text-white/70">Aucune certification externe n’est affichée ici. Urgence : 15 ou 112.</p>
                    </div>
                </section>

                <section class="lg:col-span-7 p-6 sm:p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-4">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#e2e7ff] text-[#004ac6] text-[12px] font-semibold">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">pin</span>
                                Démo visuelle
                            </div>
                            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#6ffbbe] text-[#002113] text-[12px] font-semibold">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">info</span>
                                Non branché
                            </div>
                        </div>

                        <div class="flex items-start gap-4 mt-2">
                            <div class="w-12 h-12 rounded-xl bg-[#004ac6]/10 text-[#004ac6] flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">security_update_good</span>
                            </div>
                            <div>
                                <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold">Vérification en deux étapes</h2>
                                <p class="text-[15px] leading-6 text-[#434655] mt-1">
                                    Aucun code n’a été envoyé par SMS ni par e-mail. Les six cases ci-dessous servent uniquement à l’aperçu.
                                </p>
                            </div>
                        </div>

                        <form class="mt-6 space-y-4" id="otpForm" @submit.prevent="showToast('Code 2FA non vérifié — démo uniquement. Utilisez la connexion e-mail.')">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-3">
                                    <label class="text-[14px] font-semibold" for="otp-0">Code de confirmation à usage unique</label>
                                    <button class="text-[12px] text-[#004ac6] inline-flex items-center gap-1 font-semibold"
                                            type="button"
                                            @click="showToast('Aucun code n’a été envoyé — rien à coller.')">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">content_paste</span>
                                        Coller le code
                                    </button>
                                </div>
                                <div class="grid grid-cols-6 gap-2 sm:gap-3 py-2">
                                    @foreach(range(0, 5) as $i)
                                        <input class="w-full h-14 sm:h-16 text-center font-display text-[28px] sm:text-[32px] leading-none text-[#131b2e] bg-[#f2f3ff] rounded-lg focus:bg-white focus:shadow-md outline-none focus:ring-2 focus:ring-[#004ac6]"
                                               id="otp-{{ $i }}"
                                               type="text"
                                               inputmode="numeric"
                                               maxlength="1"
                                               pattern="[0-9]*"
                                               autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                               aria-label="Chiffre {{ $i + 1 }} sur 6"
                                               x-on:input="moveOtp({{ $i }}, $event)"
                                               x-on:keydown="if ($event.key === 'Backspace' && !$event.target.value && $event.target.previousElementSibling) $event.target.previousElementSibling.focus()">
                                    @endforeach
                                </div>
                                <p class="min-h-[20px] text-[12px] text-[#434655] flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-[#006a61]" aria-hidden="true">info</span>
                                    Saisir un code ici n’ouvre aucun compte.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-[#f2f3ff] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">hourglass_top</span>
                                    <p class="text-[13px] leading-5">Aucun code n’expire ici : rien n’a été envoyé.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button class="text-[12px] text-[#004ac6] font-semibold flex items-center gap-1" type="button" @click="simulateSmsReset()">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">sync</span>
                                        Renvoyer par SMS
                                    </button>
                                    <span class="text-[#c3c6d7]" aria-hidden="true">•</span>
                                    <button class="text-[12px] text-[#434655] font-semibold flex items-center gap-1" type="button"
                                            @click="showToast('Appel vocal non configuré — aperçu visuel uniquement.')">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">call</span>
                                        Appel vocal
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-xl bg-[#eaedff] p-3.5 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[#006a61] text-[20px]" aria-hidden="true">vpn_key</span>
                                        <span class="font-display text-[16px] leading-6 font-semibold">Méthode alternative</span>
                                    </div>
                                    <span class="text-[11px] uppercase px-2 py-0.5 rounded bg-[#e2e7ff] text-[#434655] font-semibold">Aperçu</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                    <button class="w-full text-left p-2.5 rounded-lg bg-white hover:bg-[#e2e7ff] flex items-center justify-between shadow-sm" type="button"
                                            @click="showToast('Authenticator non configuré — aperçu visuel uniquement.')">
                                        <span class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[#004ac6] text-[18px]" aria-hidden="true">phonelink_lock</span>
                                            <span class="text-[12px] font-semibold">Application d’authentification</span>
                                        </span>
                                        <span class="material-symbols-outlined text-[18px] text-[#434655]" aria-hidden="true">chevron_right</span>
                                    </button>
                                    <button class="w-full text-left p-2.5 rounded-lg bg-white hover:bg-[#e2e7ff] flex items-center justify-between shadow-sm" type="button"
                                            @click="showToast('Clé de sécurité non configurée — aperçu visuel uniquement.')">
                                        <span class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[#006a61] text-[18px]" aria-hidden="true">key</span>
                                            <span class="text-[12px] font-semibold">Clé de sécurité</span>
                                        </span>
                                        <span class="material-symbols-outlined text-[18px] text-[#434655]" aria-hidden="true">chevron_right</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 pt-1">
                                <div class="flex items-center h-5">
                                    <input class="w-4 h-4 rounded accent-[#004ac6] cursor-pointer" id="trust-device" type="checkbox">
                                </div>
                                <label class="text-[13px] leading-5 cursor-pointer select-none" for="trust-device">
                                    Faire confiance à cet appareil n’est pas enregistré ici.
                                    <span class="text-[#434655]">Aucun navigateur n’est mémorisé.</span>
                                </label>
                            </div>

                            <div class="pt-2 space-y-3">
                                <button class="w-full h-12 rounded-lg bg-[#2563eb] hover:bg-[#004ac6] text-white font-display text-[16px] font-semibold shadow-md flex items-center justify-center gap-2" type="submit">
                                    <span>Valider et accéder à mon Espace</span>
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                                </button>
                                <a class="w-full h-11 rounded-lg bg-[#eaedff] hover:bg-[#e2e7ff] font-semibold text-[14px] flex items-center justify-center gap-2" href="{{ route('login') }}">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                                    <span>Annuler et revenir à la page de connexion</span>
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 p-3.5 rounded-xl bg-[#f2f3ff] flex items-start gap-3">
                        <span class="material-symbols-outlined text-[#004ac6] text-[22px] shrink-0 mt-0.5" aria-hidden="true">contact_support</span>
                        <p class="text-[13px] text-[#434655] leading-5">
                            Pour accéder à un compte existant, revenez à la connexion e-mail. En urgence vitale, appelez le 15 ou le 112.
                        </p>
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
