<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-icons')
    <title>SantéConnect — Mot de passe oublié</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#faf8ff] font-sans text-[#131b2e] min-h-screen flex flex-col selection:bg-[#dbe1ff] selection:text-[#00174b]" x-data="scDemo()">
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
                    <a href="{{ route('password.request') }}" class="text-[14px] font-semibold text-[#004ac6]" aria-current="page">Mot de passe oublié</a>
                    <a href="{{ route('landing') }}" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Retour au site</a>
                </nav>
                <span class="w-8 h-8 rounded-full bg-[#004ac6] flex items-center justify-center" aria-hidden="true">
                    <span class="material-symbols-outlined text-white text-[18px]">person</span>
                </span>
            </div>
        </div>
    </header>

    <main id="guest-content" class="w-full pt-16 sm:pt-20 flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-[1240px] py-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

            <section class="lg:col-span-5 bg-gradient-to-br from-[#004ac6] via-[#2563eb] to-[#006a61] text-white rounded-xl p-6 lg:p-8 flex flex-col justify-between shadow-xl relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#89f5e7]/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-[#0053db]/30 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col gap-6">
                    <div class="flex items-center gap-1 self-start bg-white/15 backdrop-blur-md px-3.5 py-1.5 rounded-full">
                        <span class="material-symbols-outlined text-[18px] text-[#6ffbbe]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">verified</span>
                        <span class="text-[12px] uppercase tracking-wider font-semibold">Récupération par e-mail</span>
                    </div>
                    <div>
                        <h1 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight">
                            Protection de vos données et de votre accès.
                        </h1>
                        <p class="text-[15px] leading-6 text-white/85 mt-2">
                            La réinitialisation utilise uniquement l’adresse e-mail du compte. Aucun SMS, captcha externe ni certification n’est inventé ici.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="bg-white/10 backdrop-blur-md p-4 rounded-lg flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0 text-[#6ffbbe]">
                                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">timer</span>
                            </div>
                            <div>
                                <p class="font-display text-[16px] leading-6 font-semibold">Lien à usage unique</p>
                                <p class="text-[13px] leading-5 text-white/80 mt-0.5">Le lien expire automatiquement au bout de 60 minutes.</p>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md p-4 rounded-lg flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0 text-[#89f5e7]">
                                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">mail</span>
                            </div>
                            <div>
                                <p class="font-display text-[16px] leading-6 font-semibold">Envoi si le compte existe</p>
                                <p class="text-[13px] leading-5 text-white/80 mt-0.5">Un e-mail n’est envoyé que lorsque l’adresse correspond à un compte.</p>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md p-4 rounded-lg flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0 text-[#dbe1ff]">
                                <span class="material-symbols-outlined text-[22px]" aria-hidden="true">emergency</span>
                            </div>
                            <div>
                                <p class="font-display text-[16px] leading-6 font-semibold">Urgence médicale</p>
                                <p class="text-[13px] leading-5 text-white/80 mt-0.5">En cas d’urgence vitale, appelez le 15 ou le 112. SantéConnect ne remplace pas un service d’urgence.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative z-10 pt-6 mt-6 text-white/70 text-[11px]">
                    <div class="flex items-center gap-1 text-white">
                        <span class="material-symbols-outlined text-[16px] text-[#6ffbbe]" aria-hidden="true">lock</span>
                        <span class="font-semibold">Session protégée côté serveur</span>
                    </div>
                    <p class="mt-1">Aucune certification externe n’est affichée ici.</p>
                </div>
            </section>

            <section class="lg:col-span-7 bg-white rounded-xl p-6 lg:p-8 shadow-md flex flex-col justify-between">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-[14px] font-semibold text-[#004ac6] hover:text-[#003ea8]">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                            Retour à la connexion
                        </a>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#e2e7ff] text-[#434655] text-[11px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#006a61]"></span>
                            Lien envoyé par e-mail
                        </span>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#e2e7ff] text-[#004ac6] flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[26px]" aria-hidden="true">vpn_key_alert</span>
                        </div>
                        <div>
                            <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold tracking-tight">Mot de passe oublié ?</h2>
                            <p class="text-[15px] leading-6 text-[#434655] mt-1">
                                Saisissez l’adresse e-mail rattachée à votre compte. Si elle existe, un lien de réinitialisation vous sera envoyé.
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[12px] text-[#434655] uppercase tracking-wider font-semibold mb-2">Canal de transmission</p>
                        <div class="grid grid-cols-2 p-1 bg-[#f2f3ff] rounded-lg gap-1" role="tablist">
                            <button type="button" role="tab" aria-selected="true"
                                    class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-[14px] font-semibold bg-white text-[#004ac6] shadow-sm">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">mail</span>
                                Par E-mail
                            </button>
                            <button type="button" role="tab" aria-selected="false"
                                    class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]"
                                    @click="simulateSmsReset()">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">sms</span>
                                Par SMS (Mobile)
                            </button>
                        </div>
                    </div>

                    @if(session('status'))
                        <div class="p-4 bg-[#86f2e4]/20 rounded-lg flex items-start gap-2" role="status">
                            <span class="material-symbols-outlined text-[#006a61] text-[22px] shrink-0" aria-hidden="true">check_circle</span>
                            <div>
                                <p class="font-display text-[16px] leading-6 font-semibold text-[#131b2e]">Demande enregistrée</p>
                                <p class="text-[13px] leading-5 text-[#434655] mt-0.5">{{ session('status') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="border border-red-200 bg-red-50 rounded-xl px-4 py-3" role="alert">
                            @foreach($errors->all() as $error)
                                <p class="text-sm text-red-700">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
                        @csrf
                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-[14px] font-semibold text-[#131b2e] flex items-center justify-between gap-2">
                                <span>Adresse e-mail <span class="text-[#ba1a1a] font-bold">*</span></span>
                                <span class="text-[11px] text-[#434655] font-normal">ex: nom@exemple.com</span>
                            </label>
                            <div class="relative flex items-center">
                                <span class="material-symbols-outlined absolute left-3 text-[#434655] text-[20px] pointer-events-none" aria-hidden="true">alternate_email</span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                       placeholder="nom@exemple.com"
                                       class="w-full h-11 pl-10 pr-4 bg-[#f2f3ff] focus:bg-white rounded-lg text-[15px] text-[#131b2e] placeholder:text-[#737686] focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm">
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            <p class="text-[13px] text-[#434655] flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">info</span>
                                Seule l’adresse e-mail du compte est acceptée. Aucun identifiant patient ni CIN n’est géré ici.
                            </p>
                        </div>

                        <button type="submit" class="h-12 w-full bg-[#004ac6] hover:bg-[#003ea8] text-white rounded-lg text-[14px] font-semibold flex items-center justify-center gap-2 shadow-md">
                            <span>Envoyer le lien de réinitialisation</span>
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                        </button>
                    </form>

                    <p class="text-[13px] text-[#434655] flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-[#737686] mt-0.5" aria-hidden="true">mark_email_unread</span>
                        <span>Vous n’avez pas reçu le message ? Vérifiez vos courriers indésirables, puis renvoyez le formulaire.</span>
                    </p>
                </div>

                <div class="mt-6 pt-4 bg-[#f2f3ff] p-4 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#434655] text-[22px]" aria-hidden="true">contact_support</span>
                        <div>
                            <p class="text-[12px] font-semibold text-[#131b2e]">Un problème d’accès persistant ?</p>
                            <p class="text-[13px] text-[#434655]">Reconnectez-vous ou créez un compte. En urgence, appelez le 15 ou le 112.</p>
                        </div>
                    </div>
                    <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-white text-[#131b2e] hover:text-[#004ac6] text-[12px] font-semibold shadow-sm shrink-0">
                        Retour à la connexion
                    </a>
                </div>
            </section>
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
