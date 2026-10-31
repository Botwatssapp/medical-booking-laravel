@extends('layouts.admin')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')
@section('page-subtitle', 'Gérez vos informations personnelles et les paramètres de votre compte administrateur.')

@php
    $nameParts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
    $firstName = old('first_name', $nameParts[0] ?? '');
    $lastName = old('last_name', $nameParts[1] ?? '');
    $initials = collect($nameParts)
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
    $ua = (string) request()->userAgent();
    $browser = match (true) {
        str_contains($ua, 'Edg') => 'Edge',
        str_contains($ua, 'Chrome') => 'Chrome',
        str_contains($ua, 'Firefox') => 'Firefox',
        str_contains($ua, 'Safari') => 'Safari',
        default => 'Navigateur',
    };
    $os = match (true) {
        str_contains($ua, 'Windows') => 'Windows',
        str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh') => 'macOS',
        str_contains($ua, 'Linux') => 'Linux',
        str_contains($ua, 'Android') => 'Android',
        str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
        default => null,
    };
    $sessionLabel = $os ? $browser.' sur '.$os : $browser;
@endphp

@section('page-actions')
    <span class="inline-flex items-center gap-2 h-11 px-3 rounded-xl bg-[#eaedff] text-[12px] font-semibold text-[#434655] whitespace-nowrap">
        <span class="material-symbols-outlined text-[18px] text-[#006242]" aria-hidden="true">verified_user</span>
        Compte administrateur
    </span>
@endsection

@section('admin-content')
<div class="w-full max-w-5xl mx-auto flex flex-col gap-6"
     x-data="{
        showCurrent: false,
        showPassword: false,
        showConfirm: false,
        pwd: '',
        score() {
            const p = this.pwd;
            let n = 0;
            if (p.length >= 8) n++;
            if (/[A-Z]/.test(p) && /[a-z]/.test(p)) n++;
            if (/\d/.test(p)) n++;
            if (/[^A-Za-z0-9]/.test(p)) n++;
            return n;
        }
     }"
     x-init="
        const input = document.getElementById('profile_image');
        if (input) {
            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const preview = document.getElementById('avatar-preview');
                const initials = document.getElementById('avatar-initials');
                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.src = event.target.result;
                    preview.classList.remove('hidden');
                    if (initials) initials.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            });
        }
     ">

    <nav aria-label="Fil d'Ariane" class="flex items-center gap-1.5 text-[12px] leading-4 text-[#434655]">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6] transition-colors">Administration</a>
        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
        <span class="text-[#004ac6] font-semibold">Mon profil</span>
    </nav>

    @if($user->profile_image)
        <form id="remove-image-form" method="POST" action="{{ route('admin.profile.removeImage') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <div class="relative overflow-hidden bg-white rounded-xl shadow-sm p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="absolute -right-20 -top-20 w-64 h-64 rounded-full bg-[#004ac6]/5 blur-3xl pointer-events-none"></div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 z-10 min-w-0">
            <div class="relative group shrink-0">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-[#004ac6] to-[#2563eb] text-white flex items-center justify-center font-display text-[20px] font-bold tracking-wider shadow-md overflow-hidden">
                    @if($user->profile_image_url)
                        <img id="avatar-preview" src="{{ $user->profile_image_url }}" alt="Photo de profil de {{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        <span id="avatar-initials">{{ $initials !== '' ? $initials : 'A' }}</span>
                        <img id="avatar-preview" src="" alt="" class="w-full h-full object-cover hidden">
                    @endif
                </div>
                <label class="absolute inset-0 rounded-2xl bg-[#131b2e]/50 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white cursor-pointer transition-opacity" for="profile_image">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">photo_camera</span>
                    <span class="text-[10px] mt-0.5 font-medium leading-none">Modifier</span>
                </label>
                <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-[#007d55] border-2 border-white flex items-center justify-center" title="Compte actif">
                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                </span>
            </div>
            <div class="flex flex-col min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h2 class="font-display text-[20px] leading-7 font-bold text-[#131b2e]">{{ $user->name }}</h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#dbe1ff] text-[#00174b] text-[11px] font-semibold">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">shield_person</span>
                        Administrateur
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[#434655] text-[11px] font-semibold">
                        Compte #{{ $user->id }}
                    </span>
                </div>
                <p class="text-[15px] leading-6 text-[#434655] mb-2 truncate">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[11px] text-[#737686]">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px] text-[#006242]" aria-hidden="true">history</span>
                        Membre depuis <strong class="text-[#131b2e] font-medium">{{ $user->created_at?->locale('fr')->translatedFormat('d M Y') }}</strong>
                    </span>
                    @if($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 text-[#006242] font-semibold">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check</span>
                            E-mail vérifié
                        </span>
                    @endif
                </div>
                <p class="text-[12px] text-[#434655] mt-2">JPEG, PNG ou WebP — 2 Mo maximum.
                    @if($user->profile_image)
                        <button type="button"
                                class="text-[#ba1a1a] hover:underline ml-1"
                                onclick="confirm('Supprimer la photo de profil ?') && document.getElementById('remove-image-form').submit()">
                            Supprimer la photo
                        </button>
                    @endif
                </p>
            </div>
        </div>
        <div class="z-10 w-full md:w-auto p-3.5 bg-[#f2f3ff] rounded-xl flex items-center justify-between md:flex-col md:items-end gap-1">
            <span class="text-[11px] uppercase tracking-wider text-[#737686] font-semibold">Rôle du compte</span>
            <span class="text-[14px] font-bold text-[#004ac6] inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">verified</span>
                Administrateur
            </span>
            <span class="text-[12px] text-[#434655]">Espace connecté SantéConnect</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-8 flex flex-col gap-6 min-w-0">
            <section class="bg-white rounded-xl shadow-sm p-6 sm:p-7">
                <div class="flex items-center gap-3 pb-4">
                    <div class="w-10 h-10 rounded-lg bg-[#dbe1ff] flex items-center justify-center text-[#004ac6] shrink-0">
                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">account_circle</span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Informations personnelles</h3>
                        <p class="text-[13px] leading-5 text-[#434655]">Mettez à jour le nom et l’adresse e-mail associés à votre compte administrateur.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-5">
                    @csrf
                    @method('PATCH')
                    <input accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" id="profile_image" name="profile_image" type="file">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-semibold text-[#131b2e]" for="first_name">
                                Prénom <span class="text-[#ba1a1a]">*</span>
                            </label>
                            <input class="w-full h-11 px-3.5 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 focus:bg-white"
                                   id="first_name" name="first_name" required type="text" value="{{ $firstName }}">
                            @error('first_name')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                            @error('name')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-semibold text-[#131b2e]" for="last_name">Nom de famille</label>
                            <input class="w-full h-11 px-3.5 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 focus:bg-white"
                                   id="last_name" name="last_name" type="text" value="{{ $lastName }}">
                            @error('last_name')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <label class="text-[12px] font-semibold text-[#131b2e]" for="email">
                                Adresse e-mail professionnelle <span class="text-[#ba1a1a]">*</span>
                            </label>
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center gap-1 text-[11px] text-[#006242] font-semibold bg-[#006242]/10 px-2 py-0.5 rounded-full">
                                    <span class="material-symbols-outlined text-[13px]" aria-hidden="true">check</span>
                                    Vérifiée
                                </span>
                            @endif
                        </div>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-[#737686] text-[18px]" aria-hidden="true">mail</span>
                            <input class="w-full h-11 pl-10 pr-3.5 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 focus:bg-white"
                                   id="email" name="email" required type="email" value="{{ old('email', $user->email) }}">
                        </div>
                        <p class="text-[12px] text-[#434655] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[#737686] text-[14px]" aria-hidden="true">info</span>
                            Identifiant de connexion de ce compte.
                        </p>
                        @error('email')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[12px] font-semibold text-[#131b2e]" for="phone">Numéro de téléphone</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3.5 text-[#737686] text-[18px]" aria-hidden="true">call</span>
                            <input class="w-full h-11 pl-10 pr-3.5 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 focus:bg-white"
                                   id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <span class="text-[12px] text-[#737686]">Optionnel. Visible uniquement sur ce compte.</span>
                        @error('phone')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="pt-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <button class="h-11 px-6 bg-[#004ac6] text-white font-semibold rounded-lg hover:bg-[#003ea8] shadow-sm flex items-center gap-2" type="submit">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">save</span>
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </section>

            <section class="bg-white rounded-xl shadow-sm p-6 sm:p-7">
                <div class="flex items-center gap-3 pb-4">
                    <div class="w-10 h-10 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#004ac6] shrink-0">
                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">lock</span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Mettre à jour le mot de passe</h3>
                        <p class="text-[13px] leading-5 text-[#434655]">Laissez vide si vous ne souhaitez pas changer le mot de passe. Minimum 8 caractères.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-4 flex flex-col gap-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="update_password" value="1">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[12px] font-semibold text-[#131b2e]" for="current_password">
                            Mot de passe actuel <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-3.5 pr-11 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 focus:bg-white"
                                   id="current_password" name="current_password" placeholder="••••••••••••" required
                                   :type="showCurrent ? 'text' : 'password'" autocomplete="current-password">
                            <button class="absolute right-3 text-[#737686] hover:text-[#131b2e] p-1" type="button" @click="showCurrent = !showCurrent" title="Afficher ou masquer">
                                <span class="material-symbols-outlined text-[20px]" x-text="showCurrent ? 'visibility_off' : 'visibility'" aria-hidden="true">visibility</span>
                            </button>
                        </div>
                        @error('current_password')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[12px] font-semibold text-[#131b2e]" for="password">
                            Nouveau mot de passe <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-3.5 pr-11 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 focus:bg-white"
                                   id="password" name="password" required x-model="pwd"
                                   :type="showPassword ? 'text' : 'password'" autocomplete="new-password">
                            <button class="absolute right-3 text-[#737686] hover:text-[#131b2e] p-1" type="button" @click="showPassword = !showPassword" title="Afficher ou masquer">
                                <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'" aria-hidden="true">visibility</span>
                            </button>
                        </div>
                        <div class="mt-1 flex flex-col gap-1.5" x-show="pwd.length > 0" x-cloak>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-[#434655] font-medium">Robustesse</span>
                                <span class="text-[11px] font-bold" :class="score() >= 3 ? 'text-[#006242]' : 'text-[#434655]'" x-text="score() >= 3 ? 'Fort' : (score() >= 2 ? 'Moyen' : 'Faible')"></span>
                            </div>
                            <div class="w-full h-1.5 bg-[#eaedff] rounded-full overflow-hidden flex gap-1">
                                <div class="h-full w-1/4 rounded-full" :class="score() >= 1 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                                <div class="h-full w-1/4 rounded-full" :class="score() >= 2 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                                <div class="h-full w-1/4 rounded-full" :class="score() >= 3 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                                <div class="h-full w-1/4 rounded-full" :class="score() >= 4 ? 'bg-[#006242]' : 'bg-[#c3c6d7]'"></div>
                            </div>
                        </div>
                        @error('password')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[12px] font-semibold text-[#131b2e]" for="password_confirmation">
                            Confirmer le nouveau mot de passe <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input class="w-full h-11 pl-3.5 pr-11 rounded-lg bg-[#f2f3ff] text-[15px] text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 focus:bg-white"
                                   id="password_confirmation" name="password_confirmation" required
                                   :type="showConfirm ? 'text' : 'password'" autocomplete="new-password">
                            <button class="absolute right-3 text-[#737686] hover:text-[#131b2e] p-1" type="button" @click="showConfirm = !showConfirm" title="Afficher ou masquer">
                                <span class="material-symbols-outlined text-[20px]" x-text="showConfirm ? 'visibility_off' : 'visibility'" aria-hidden="true">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="pt-2 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold"
                              :class="pwd.length >= 8 ? 'bg-[#006242]/10 text-[#006242]' : 'bg-[#f2f3ff] text-[#434655]'">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true" x-text="pwd.length >= 8 ? 'done' : 'radio_button_unchecked'">radio_button_unchecked</span>
                            8 caractères minimum
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold"
                              :class="/[A-Z]/.test(pwd) && /[a-z]/.test(pwd) ? 'bg-[#006242]/10 text-[#006242]' : 'bg-[#f2f3ff] text-[#434655]'">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true" x-text="/[A-Z]/.test(pwd) && /[a-z]/.test(pwd) ? 'done' : 'radio_button_unchecked'">radio_button_unchecked</span>
                            Majuscule et minuscule
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold"
                              :class="/\d/.test(pwd) && /[^A-Za-z0-9]/.test(pwd) ? 'bg-[#006242]/10 text-[#006242]' : 'bg-[#f2f3ff] text-[#434655]'">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true" x-text="/\d/.test(pwd) && /[^A-Za-z0-9]/.test(pwd) ? 'done' : 'radio_button_unchecked'">radio_button_unchecked</span>
                            Chiffres et symboles
                        </span>
                    </div>
                    <div class="pt-3">
                        <button class="h-11 px-6 bg-[#eaedff] text-[#131b2e] hover:bg-[#e2e7ff] font-semibold rounded-lg shadow-sm flex items-center gap-2" type="submit">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">key</span>
                            Mettre à jour le mot de passe
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6 min-w-0">
            <section class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="w-10 h-10 rounded-lg bg-[#006242]/10 text-[#006242] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">phonelink_lock</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#f2f3ff] text-[#434655] text-[11px] font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#737686]"></span>
                        Non configurée
                    </span>
                </div>
                <div>
                    <h3 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Authentification en deux étapes</h3>
                    <p class="text-[13px] leading-5 text-[#434655] mt-1">
                        Cette option n’est pas activée sur le compte. L’aperçu ci-dessous reste visuel uniquement.
                    </p>
                </div>
                <div class="p-3 bg-[#f2f3ff] rounded-lg flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#006a61] text-[24px]" aria-hidden="true">verified</span>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[12px] font-semibold text-[#131b2e]">Code temporaire</span>
                        <span class="text-[11px] text-[#737686]">Aucun générateur n’est lié à ce compte</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 pt-1">
                    <button class="w-full h-10 px-3 bg-[#eaedff] hover:bg-[#e2e7ff] text-[#131b2e] text-[12px] font-semibold rounded-lg flex items-center justify-center gap-2" type="button"
                            @click="$dispatch('admin-toast', 'Codes de secours non configurés — aperçu visuel.')">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">visibility</span>
                        Voir les codes de secours
                    </button>
                    <button class="w-full h-10 px-3 text-[#737686] hover:text-[#ba1a1a] hover:bg-[#ffdad6]/30 text-[12px] font-semibold rounded-lg flex items-center justify-center gap-2" type="button"
                            @click="$dispatch('admin-toast', 'Réinitialisation de clé non configurée — aperçu visuel.')">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">restart_alt</span>
                        Réinitialiser la clé
                    </button>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="font-display text-[16px] leading-6 font-bold text-[#131b2e]">Sessions actives</h4>
                    <span class="px-2 py-0.5 rounded-full bg-[#eaedff] text-[11px] text-[#434655] font-medium">1 session</span>
                </div>
                <div class="p-3.5 bg-[#f2f3ff] rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-[#004ac6] text-[22px] mt-0.5" aria-hidden="true">laptop_mac</span>
                    <div class="flex flex-col flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[12px] font-bold text-[#131b2e]">{{ $sessionLabel }}</span>
                            <span class="px-1.5 py-0.5 rounded bg-[#007d55]/15 text-[#006242] text-[10px] font-bold uppercase">Ce navigateur</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1 text-[11px] text-[#737686]">
                            <span class="w-2 h-2 rounded-full bg-[#007d55]"></span>
                            En ligne maintenant
                            @if(request()->ip())
                                · {{ request()->ip() }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-[#eaedff] flex flex-col gap-2">
                    <p class="text-[12px] text-[#737686] leading-tight">
                        Seule la session en cours est affichée. Les autres appareils ne sont pas listés dans cette version.
                    </p>
                    <button class="mt-1 text-left text-[12px] font-semibold text-[#ba1a1a] hover:underline inline-flex items-center gap-1.5" type="button"
                            @click="$dispatch('admin-toast', 'Déconnexion des autres sessions non configurée — aperçu visuel.')">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">logout</span>
                        Déconnecter toutes les autres sessions
                    </button>
                </div>
            </section>

            <div class="p-4 bg-gradient-to-br from-[#e2e7ff] to-[#eaedff] rounded-xl flex items-center gap-3">
                <span class="material-symbols-outlined text-[#004ac6] text-[28px]" aria-hidden="true">verified</span>
                <div class="flex flex-col min-w-0">
                    <span class="text-[12px] font-bold text-[#131b2e]">Compte administrateur</span>
                    <span class="text-[11px] text-[#434655]">Espace connecté SantéConnect</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
