@php
    $resetUser ??= null;
    $expireMinutes ??= (int) config('auth.passwords.users.expire', 60);
    $email = old('email', $request->email);
    $displayName = $resetUser?->name;
    $roleLabel = match ($resetUser?->role) {
        'doctor' => 'Médecin',
        'admin' => 'Administrateur',
        'patient' => 'Patient',
        default => null,
    };
    $initials = 'SC';
    if ($displayName) {
        $parts = preg_split('/\s+/', trim($displayName)) ?: [];
        $initials = mb_strtoupper(
            mb_substr($parts[0] ?? 'S', 0, 1).mb_substr($parts[count($parts) - 1] ?? 'C', 0, 1)
        );
    }
    $maskedEmail = $email;
    if (is_string($email) && str_contains($email, '@')) {
        [$local, $domain] = explode('@', $email, 2);
        $maskedEmail = mb_substr($local, 0, 1).'***@'.$domain;
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head-icons')
    <title>SantéConnect — Nouveau mot de passe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-[#faf8ff] font-sans text-[#131b2e] min-h-screen flex flex-col justify-between selection:bg-[#dbe1ff] selection:text-[#00174b]"
      x-data="scDemo()">
    <a href="#password-reset-form" class="sc-skip-link">Aller au contenu</a>

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

    <main class="w-full pt-16 sm:pt-20 flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8" x-data="{ showPw: false, showPw2: false, password: '' }">
        <div class="max-w-[1240px] w-full mx-auto py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 rounded-xl bg-white shadow-xl overflow-hidden">
                <section class="lg:col-span-5 bg-gradient-to-br from-[#004ac6] via-[#2563eb] to-[#006a61] p-6 lg:p-8 text-white flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-[#89f5e7]/10 blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-16 -bottom-16 w-72 h-72 rounded-full bg-[#b4c5ff]/15 blur-2xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col gap-6">
                        <div class="inline-flex items-center gap-2 self-start px-3 py-1.5 rounded-full bg-white/15 backdrop-blur-md">
                            <span class="w-2 h-2 rounded-full bg-[#89f5e7] animate-pulse"></span>
                            <span class="text-[11px] tracking-wide uppercase font-semibold">Lien de réinitialisation reçu</span>
                        </div>
                        <div class="space-y-2">
                            <h1 class="font-display text-[28px] lg:text-[40px] leading-9 lg:leading-[48px] font-bold tracking-tight">
                                Définissez un nouveau mot de passe pour votre compte.
                            </h1>
                            <p class="text-[15px] leading-6 text-white/85">
                                Le lien reçu par e-mail est à usage unique. Le mot de passe est enregistré de façon hachée. SantéConnect ne le stocke pas en clair.
                            </p>
                        </div>
                        <div class="space-y-3">
                            <div class="p-4 rounded-lg bg-white/10">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[20px] text-[#89f5e7]" aria-hidden="true">timer</span>
                                    </div>
                                    <div>
                                        <h2 class="font-display text-[16px] leading-6 font-semibold">Lien limité dans le temps</h2>
                                        <p class="text-[13px] leading-5 text-white/80 mt-1">Le lien expire automatiquement au bout de {{ $expireMinutes }} minutes.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded-lg bg-white/10">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[20px] text-[#89f5e7]" aria-hidden="true">link_off</span>
                                    </div>
                                    <div>
                                        <h2 class="font-display text-[16px] leading-6 font-semibold">Usage unique</h2>
                                        <p class="text-[13px] leading-5 text-white/80 mt-1">Après enregistrement, ce lien ne peut plus servir à changer le mot de passe.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded-lg bg-white/10">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-[20px] text-[#89f5e7]" aria-hidden="true">devices</span>
                                    </div>
                                    <div>
                                        <h2 class="font-display text-[16px] leading-6 font-semibold">Autres sessions</h2>
                                        <p class="text-[13px] leading-5 text-white/80 mt-1">Les autres sessions ouvertes ne sont pas fermées automatiquement ici.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative z-10 pt-6 mt-6 space-y-2">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white/15 text-white">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-[#89f5e7]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">mail</span>
                                <span class="text-[12px] font-semibold">Ouvert depuis le lien e-mail</span>
                            </div>
                            <span class="text-[12px] font-semibold bg-white/20 px-2 py-0.5 rounded-full">{{ $expireMinutes }} min</span>
                        </div>
                        <p class="text-[11px] text-white/70 px-1">Aucune certification externe n’est affichée ici. Urgence : 15 ou 112.</p>
                    </div>
                </section>

                <section class="lg:col-span-7 p-6 lg:p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-6">
                            <a class="inline-flex items-center gap-1.5 text-[14px] font-semibold text-[#004ac6] hover:text-[#003ea8]" href="{{ route('login') }}">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
                                Retour à la connexion
                            </a>
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#86f2e4]/20 text-[#005049] text-[11px] font-semibold">
                                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">lock</span>
                                Lien e-mail
                            </div>
                        </div>

                        <div class="space-y-1 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-[#eaedff] flex items-center justify-center mb-2 text-[#004ac6]">
                                <span class="material-symbols-outlined text-[26px]" aria-hidden="true">key</span>
                            </div>
                            <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 font-bold">Nouveau mot de passe</h2>
                            <p class="text-[15px] leading-6 text-[#434655] max-w-xl">
                                Le mot de passe doit contenir au moins 8 caractères. Aucune ordonnance ni résultat n’est stocké ici.
                            </p>
                        </div>

                        @if($displayName || $email)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-[#f2f3ff] mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#004ac6] flex items-center justify-center text-white font-display text-[16px] font-bold">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-[14px] font-semibold">{{ $displayName ?: 'Compte SantéConnect' }}</span>
                                            @if($roleLabel)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#dae2fd] text-[#434655] text-[11px] font-semibold">{{ $roleLabel }}</span>
                                            @endif
                                        </div>
                                        <span class="text-[13px] text-[#434655]">{{ $maskedEmail }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="border border-red-200 bg-red-50 rounded-xl px-4 py-3 mb-5" role="alert">
                                @foreach($errors->all() as $error)
                                    <p class="text-sm text-red-700">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form class="space-y-4" id="password-reset-form" method="POST" action="{{ route('password.store') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="space-y-1.5">
                                <label class="text-[14px] font-semibold" for="email">Adresse e-mail <span class="text-[#ba1a1a]">*</span></label>
                                <input class="w-full h-11 px-3.5 bg-[#f2f3ff] rounded-lg text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6]"
                                       id="email" name="email" type="email" value="{{ $email }}" required autocomplete="username">
                                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <label class="text-[14px] font-semibold" for="password">Nouveau mot de passe <span class="text-[#ba1a1a]">*</span></label>
                                    <span class="text-[12px] font-semibold" :class="password.length >= 8 ? 'text-[#006242]' : 'text-[#434655]'"
                                          x-text="password.length >= 8 ? 'Au moins 8 caractères' : 'Minimum 8 caractères'"></span>
                                </div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#737686]">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock_reset</span>
                                    </span>
                                    <input class="w-full h-11 pl-11 pr-11 bg-[#f2f3ff] rounded-lg text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6]"
                                           id="password" name="password" :type="showPw ? 'text' : 'password'" x-model="password"
                                           required autocomplete="new-password" placeholder="••••••••">
                                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#737686]"
                                            @click="showPw = !showPw"
                                            :aria-label="showPw ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                                        <span class="material-symbols-outlined text-[20px]" x-text="showPw ? 'visibility_off' : 'visibility'" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                                <div class="grid grid-cols-4 gap-1.5 pt-1">
                                    <div class="h-1.5 rounded-full" :class="password.length >= 2 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                                    <div class="h-1.5 rounded-full" :class="password.length >= 4 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                                    <div class="h-1.5 rounded-full" :class="password.length >= 6 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                                    <div class="h-1.5 rounded-full" :class="password.length >= 8 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                                </div>
                                <p class="text-[11px] text-[#434655] pt-2 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]" :class="password.length >= 8 ? 'text-[#006242]' : 'text-[#737686]'" aria-hidden="true">check_circle</span>
                                    Au moins 8 caractères
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[14px] font-semibold" for="password_confirmation">
                                    Confirmer le nouveau mot de passe <span class="text-[#ba1a1a]">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#737686]">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock_clock</span>
                                    </span>
                                    <input class="w-full h-11 pl-11 pr-11 bg-[#f2f3ff] rounded-lg text-[15px] focus:outline-none focus:ring-2 focus:ring-[#004ac6]"
                                           id="password_confirmation" name="password_confirmation" :type="showPw2 ? 'text' : 'password'"
                                           required autocomplete="new-password" placeholder="••••••••">
                                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#737686]"
                                            @click="showPw2 = !showPw2"
                                            :aria-label="showPw2 ? 'Masquer la confirmation' : 'Afficher la confirmation'">
                                        <span class="material-symbols-outlined text-[20px]" x-text="showPw2 ? 'visibility_off' : 'visibility'" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>

                            <p class="p-3 rounded-lg bg-[#f2f3ff] text-[13px] text-[#434655]">
                                Les autres appareils déjà connectés ne sont pas déconnectés automatiquement.
                            </p>

                            <div class="pt-1 space-y-2">
                                <button class="w-full h-11 px-6 rounded-lg bg-[#004ac6] hover:bg-[#003ea8] text-white text-[14px] font-semibold flex items-center justify-center gap-2 shadow-md" type="submit">
                                    <span>Enregistrer le mot de passe</span>
                                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">arrow_forward</span>
                                </button>
                                <div class="text-center">
                                    <a class="text-[12px] text-[#434655] hover:text-[#ba1a1a] inline-block py-1" href="{{ route('login') }}">Annuler la réinitialisation</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 p-3 rounded-lg bg-[#eaedff] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-white flex items-center justify-center text-[#004ac6] shrink-0">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">support_agent</span>
                        </div>
                        <p class="text-[13px] text-[#434655] leading-5">
                            <span class="font-semibold text-[#131b2e]">Besoin d’aide ?</span>
                            Demandez un nouveau lien ou reconnectez-vous. En urgence vitale, appelez le 15 ou le 112.
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
