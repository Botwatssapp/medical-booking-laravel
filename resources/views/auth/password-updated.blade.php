@php
    $displayName = $name ?: 'Compte SantéConnect';
    $roleLabel = match ($role) {
        'doctor' => 'Médecin',
        'admin' => 'Administrateur',
        'patient' => 'Patient',
        default => 'Compte',
    };
    $headingName = $role === 'doctor' ? 'Dr. '.$displayName : $displayName;
    $parts = preg_split('/\s+/', trim($displayName)) ?: [];
    $initials = mb_strtoupper(
        mb_substr($parts[0] ?? 'S', 0, 1).mb_substr($parts[count($parts) - 1] ?? 'C', 0, 1)
    );
    $maskedEmail = 'Adresse non affichée';
    if (is_string($email) && str_contains($email, '@')) {
        [$local, $domain] = explode('@', $email, 2);
        $maskedEmail = mb_substr($local, 0, 1).'***@'.$domain;
    }
    $updatedAt = now()->locale('fr')->isoFormat('D MMMM YYYY [à] HH:mm');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-icons')
    <title>SantéConnect — Mot de passe modifié</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#faf8ff] font-sans text-[#131b2e] min-h-screen flex flex-col justify-between selection:bg-[#dbe1ff] selection:text-[#00174b]"
      x-data="scDemo()">
    <a href="#guest-content" class="sc-skip-link">Aller au contenu</a>

    <header class="fixed top-0 w-full z-50 bg-[#faf8ff]/85 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.03)]">
        <div class="h-16 sm:h-20 sc-page-wide flex items-center justify-between gap-3">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 min-w-0">
                <span class="w-8 h-8 rounded-lg bg-[#2563eb] text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">favorite</span>
                </span>
                <span class="flex flex-col min-w-0">
                    <span class="font-display text-[16px] leading-6 font-bold text-[#131b2e] tracking-tight truncate">SantéConnect</span>
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
                    <a href="{{ route('demo.two-factor') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Vérification 2FA</a>
                    <a href="{{ route('password.request') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Mot de passe oublié</a>
                    <a href="{{ route('landing') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Retour au site</a>
                </nav>
                <span class="w-8 h-8 rounded-full bg-[#004ac6] flex items-center justify-center" aria-hidden="true">
                    <span class="material-symbols-outlined text-white text-[18px]">person</span>
                </span>
            </div>
        </div>
    </header>

    <main id="guest-content" class="w-full pt-16 sm:pt-20 flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8" x-data="scResetSuccess(@js(route('login')))">
        <div class="w-full max-w-[1240px] mx-auto py-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex flex-wrap items-center gap-2 text-[12px] font-semibold text-[#434655]">
                    <span class="flex items-center gap-1.5 text-[#006242]">
                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">check_circle</span>
                        1. Demande
                    </span>
                    <span class="text-[#c3c6d7]">/</span>
                    <span class="flex items-center gap-1.5 text-[#006242]">
                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">check_circle</span>
                        2. Lien e-mail
                    </span>
                    <span class="text-[#c3c6d7]">/</span>
                    <span class="flex items-center gap-1.5 text-[#006242] font-semibold">
                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">task_alt</span>
                        3. Mot de passe mis à jour
                    </span>
                </div>
                <div class="hidden md:flex items-center gap-2 bg-[#e2e7ff] px-3 py-1 rounded-full text-[11px] text-[#434655]">
                    <span class="w-2 h-2 rounded-full bg-[#006242] animate-pulse"></span>
                    Lien de réinitialisation consommé
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                <section class="lg:col-span-5 bg-gradient-to-br from-[#004ac6] via-[#2563eb] to-[#006a61] text-white rounded-xl p-6 lg:p-8 flex flex-col justify-between shadow-xl relative overflow-hidden">
                    <div class="absolute -top-24 -left-24 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-[#86f2e4]/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-md px-3 py-1 rounded-full text-[11px] mb-6">
                            <span class="material-symbols-outlined text-[15px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">verified</span>
                            <span class="tracking-wide uppercase font-semibold">Réinitialisation confirmée</span>
                        </div>
                        <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight mb-4">
                            Votre mot de passe a été mis à jour avec succès.
                        </h1>
                        <p class="text-[15px] leading-6 text-white/85 mb-8">
                            Vous pouvez vous connecter avec ce nouveau mot de passe. SantéConnect ne stocke pas le mot de passe en clair.
                        </p>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 bg-white/10 p-3.5 rounded-lg backdrop-blur-sm">
                                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">link_off</span>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-display text-[16px] leading-6 font-semibold mb-0.5">Lien devenu inutilisable</h2>
                                    <p class="text-[13px] leading-5 text-white/80">Le lien reçu par e-mail ne peut plus servir à changer le mot de passe.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 bg-white/10 p-3.5 rounded-lg backdrop-blur-sm">
                                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">login</span>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-display text-[16px] leading-6 font-semibold mb-0.5">Connexion requise</h2>
                                    <p class="text-[13px] leading-5 text-white/80">Aucun e-mail d’audit supplémentaire n’est envoyé ici. Reconnectez-vous pour ouvrir votre espace.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 bg-white/10 p-3.5 rounded-lg backdrop-blur-sm">
                                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">password</span>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-display text-[16px] leading-6 font-semibold mb-0.5">Mot de passe haché</h2>
                                    <p class="text-[13px] leading-5 text-white/80">Seul un hash est enregistré. Aucune session n’est ouverte automatiquement après cette étape.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative z-10 pt-6 mt-6 border-t border-white/15 flex flex-col gap-1.5 text-[11px] text-white/80">
                        <div class="flex items-center gap-2 text-white">
                            <span class="material-symbols-outlined text-[16px] text-[#6ffbbe]" aria-hidden="true">lock</span>
                            <span>Session protégée côté serveur</span>
                        </div>
                        <p>Aucune certification externe n’est affichée ici. Urgence vitale : 15 ou 112.</p>
                    </div>
                </section>

                <div class="lg:col-span-7 flex flex-col justify-between gap-4">
                    <section class="bg-white rounded-xl p-6 lg:p-8 shadow-md flex flex-col">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
                            <div class="relative w-16 h-16 rounded-full bg-[#007d55] flex items-center justify-center shrink-0 shadow-md">
                                <span class="material-symbols-outlined text-[#bdffdb] text-[36px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">check_circle</span>
                                <div class="absolute -bottom-1 -right-1 bg-white p-1 rounded-full shadow-sm">
                                    <span class="material-symbols-outlined text-[#004ac6] text-[14px]" aria-hidden="true">local_hospital</span>
                                </div>
                            </div>
                            <div>
                                <div class="inline-flex items-center gap-1.5 text-[#006242] text-[12px] font-semibold mb-0.5">
                                    <span class="w-2 h-2 rounded-full bg-[#006242]"></span>
                                    Opération validée le {{ $updatedAt }}
                                </div>
                                <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight">Mot de passe modifié !</h2>
                                <p class="text-[15px] leading-6 text-[#434655] mt-1">
                                    Vous pouvez maintenant vous connecter pour consulter vos rendez-vous. Aucune ordonnance numérique ni téléconsultation n’est gérée ici.
                                </p>
                            </div>
                        </div>

                        <div class="bg-[#f2f3ff] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-[#2563eb] text-white font-display text-[16px] flex items-center justify-center font-bold">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-display text-[16px] leading-6 font-semibold">{{ $headingName }}</span>
                                        <span class="bg-[#006242]/10 text-[#006242] text-[11px] px-2 py-0.5 rounded-full font-semibold">{{ $roleLabel }}</span>
                                    </div>
                                    <p class="text-[13px] text-[#434655]">{{ $maskedEmail }}</p>
                                </div>
                            </div>
                            <div class="bg-white px-3 py-2 rounded-lg shadow-sm flex items-center gap-2 self-start sm:self-auto">
                                <span class="material-symbols-outlined text-[#006242] text-[20px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">security</span>
                                <div>
                                    <span class="text-[11px] text-[#434655] block leading-tight">État du compte</span>
                                    <span class="text-[12px] font-semibold text-[#006242]">Mot de passe mis à jour</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#e2e7ff]/60 rounded-xl p-4 mb-6 flex flex-col gap-4">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="relative w-8 h-8 flex items-center justify-center shrink-0">
                                        <svg class="w-8 h-8 -rotate-90 transform" viewBox="0 0 36 36" aria-hidden="true">
                                            <circle class="text-[#c3c6d7]/40" cx="18" cy="18" fill="none" r="14" stroke="currentColor" stroke-width="3"></circle>
                                            <circle class="text-[#004ac6]" cx="18" cy="18" fill="none" r="14" stroke="currentColor" stroke-dasharray="88" stroke-linecap="round" stroke-width="3"
                                                    :style="`stroke-dashoffset: ${dashoffset()}`"></circle>
                                        </svg>
                                        <span class="absolute font-display text-[12px] text-[#004ac6] font-bold" x-text="Math.max(seconds, 0)"></span>
                                    </div>
                                    <div>
                                        <h3 class="font-display text-[16px] leading-6 font-semibold">Redirection automatique</h3>
                                        <p class="text-[13px] text-[#434655]">
                                            Vous allez être redirigé vers la connexion
                                            <span class="font-semibold text-[#004ac6]" x-text="seconds > 1 ? seconds + ' secondes' : (seconds === 1 ? '1 seconde' : 'maintenant…')"></span>.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" class="text-[#004ac6] hover:text-[#003ea8] text-[12px] font-semibold underline py-1 px-2 rounded hover:bg-[#dae2fd]"
                                        @click="togglePause()"
                                        x-text="paused ? 'Reprendre la redirection' : 'Mettre en pause'"></button>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center gap-2">
                                <a href="{{ route('login') }}" class="w-full sm:flex-1 bg-[#004ac6] hover:bg-[#003ea8] text-white h-11 px-6 rounded-lg font-display text-[16px] flex items-center justify-center gap-2 shadow-md">
                                    <span>Se connecter</span>
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                                </a>
                                <a href="{{ route('login') }}" class="w-full sm:w-auto bg-[#eaedff] text-[#131b2e] hover:bg-[#dae2fd] h-11 px-4 rounded-lg text-[14px] font-semibold flex items-center justify-center text-center">
                                    Se connecter avec un autre compte
                                </a>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-[#dae2fd]">
                                <span class="text-[11px] text-[#434655] font-semibold">Options directes :</span>
                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-[#f2f3ff] shadow-sm text-[11px] text-[#131b2e]"
                                        @click="toast('Aucun pass PDF n’est généré après un changement de mot de passe.')">
                                    <span class="material-symbols-outlined text-[16px] text-[#004ac6]" aria-hidden="true">picture_as_pdf</span>
                                    Télécharger le Pass (PDF)
                                </button>
                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-[#f2f3ff] shadow-sm text-[11px] text-[#131b2e]"
                                        @click="sms()">
                                    <span class="material-symbols-outlined text-[16px] text-[#006a61]" aria-hidden="true">sms</span>
                                    Recevoir par SMS
                                </button>
                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-[#f2f3ff] shadow-sm text-[11px] text-[#131b2e]"
                                        @click="wallet('apple')">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">wallet</span>
                                    Ajouter à Apple Wallet
                                </button>
                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-[#f2f3ff] shadow-sm text-[11px] text-[#131b2e]"
                                        @click="wallet('google')">
                                    <span class="material-symbols-outlined text-[16px] text-[#004ac6]" aria-hidden="true">account_balance_wallet</span>
                                    Ajouter à Google Wallet
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-[#e2e7ff] pt-4">
                            <h3 class="text-[12px] text-[#434655] uppercase tracking-wider mb-2 font-semibold">Recommandations de sécurité</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="bg-[#f2f3ff] p-2.5 rounded-lg flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[#006242] text-[18px] shrink-0 mt-0.5" aria-hidden="true">verified_user</span>
                                    <span class="text-[13px] leading-5">La double authentification n’est pas branchée.</span>
                                </div>
                                <div class="bg-[#f2f3ff] p-2.5 rounded-lg flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[#004ac6] text-[18px] shrink-0 mt-0.5" aria-hidden="true">fingerprint</span>
                                    <span class="text-[13px] leading-5">Aucune clé biométrique n’est enregistrée ici.</span>
                                </div>
                                <div class="bg-[#f2f3ff] p-2.5 rounded-lg flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[#006a61] text-[18px] shrink-0 mt-0.5" aria-hidden="true">notifications_active</span>
                                    <span class="text-[13px] leading-5">Aucune alerte SMS de connexion n’est envoyée.</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="bg-[#f2f3ff] rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 text-[#434655] shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#ffdad6] text-[#93000a] flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">shield_person</span>
                            </div>
                            <div>
                                <p class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Ce n’était pas vous ?</p>
                                <p class="text-[13px] leading-5">Si vous n’êtes pas l’auteur de cette modification, demandez un nouveau lien et changez encore le mot de passe.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full md:w-auto shrink-0 justify-end">
                            <a href="{{ route('password.request') }}" class="flex items-center gap-1.5 bg-[#ba1a1a] text-white hover:opacity-95 px-4 py-2 rounded-lg text-[12px] font-semibold shadow-sm whitespace-nowrap">
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">lock_reset</span>
                                Nouveau lien
                            </a>
                            <a href="{{ route('login') }}" class="text-[#434655] hover:text-[#131b2e] p-2 rounded-lg hover:bg-[#eaedff] text-[12px] font-semibold underline whitespace-nowrap">
                                Connexion
                            </a>
                        </div>
                    </section>
                </div>
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
