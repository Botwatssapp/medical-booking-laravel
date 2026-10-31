@extends('layouts.admin')
@section('page-title', 'Ajouter un utilisateur')
@section('page-subtitle', 'Créez un nouveau compte utilisateur avec attribution de rôle sur SantéConnect.')

@php
    $oldRole = old('role', 'patient');
@endphp

@section('admin-content')
<div class="sc-stack w-full"
     x-data="{
        name: @js(old('name', '')),
        email: @js(old('email', '')),
        role: @js($oldRole),
        showPassword: false,
        showConfirm: false,
        initials() {
            const parts = String(this.name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '--';
            const first = parts[0].charAt(0);
            const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : '';
            return (first + last).toUpperCase();
        },
        roleLabel() {
            if (this.role === 'doctor') return 'Médecin';
            if (this.role === 'admin') return 'Administrateur';
            return 'Patient';
        }
     }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <nav aria-label="Fil d'Ariane" class="flex items-center gap-1 text-[11px] leading-4 text-[#434655]">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6] transition-colors">Admin</a>
                <span class="material-symbols-outlined text-[14px] text-[#737686]" aria-hidden="true">chevron_right</span>
                <a href="{{ route('admin.users.index') }}" class="hover:text-[#004ac6] transition-colors">Utilisateurs</a>
                <span class="material-symbols-outlined text-[14px] text-[#737686]" aria-hidden="true">chevron_right</span>
                <span class="text-[#004ac6] font-semibold">Ajouter</span>
            </nav>
            <h1 class="font-display text-[28px] leading-9 font-semibold tracking-tight">Ajouter un utilisateur</h1>
            <p class="text-[15px] leading-6 text-[#434655]">Créez un nouveau compte utilisateur avec attribution de rôle sur SantéConnect.</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-1 px-4 py-2.5 rounded-lg bg-white text-[#131b2e] hover:bg-[#eaedff] shadow-sm transition-all text-[14px] leading-5 font-semibold group self-start md:self-auto">
            <span class="material-symbols-outlined text-[18px] text-[#737686] group-hover:-translate-x-0.5 transition-transform" aria-hidden="true">arrow_back</span>
            <span>Retour aux utilisateurs</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        @csrf

        <div class="lg:col-span-8 space-y-6">
            <section class="bg-white rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-[#dbe1ff] flex items-center justify-center text-[#004ac6]">
                            <span class="material-symbols-outlined text-[22px]" aria-hidden="true">person_outline</span>
                        </div>
                        <div>
                            <h2 class="font-display text-[16px] leading-6 font-semibold">Informations personnelles</h2>
                            <p class="text-[11px] leading-4 text-[#434655]">Nom, e-mail et téléphone enregistrés sur le compte</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-[12px] leading-4 font-semibold text-[#131b2e]" for="name">
                            Nom <span class="text-[#ba1a1a] font-bold">*</span>
                        </label>
                        <input class="w-full h-11 px-3.5 bg-white rounded-lg text-[13px] leading-5 text-[#131b2e] placeholder:text-[#737686]/70 focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm @error('name') ring-2 ring-[#ba1a1a] @enderror"
                               id="name"
                               name="name"
                               type="text"
                               required
                               maxlength="255"
                               placeholder="Nom complet"
                               value="{{ old('name') }}"
                               x-model="name">
                        @error('name') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-[12px] leading-4 font-semibold text-[#131b2e]" for="email">
                                Adresse e-mail <span class="text-[#ba1a1a] font-bold">*</span>
                            </label>
                            <span class="text-[11px] leading-4 text-[#006242] flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">verified</span> Unique au système
                            </span>
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">alternate_email</span>
                            <input class="w-full h-11 pl-10 pr-3.5 bg-white rounded-lg text-[13px] leading-5 text-[#131b2e] placeholder:text-[#737686]/70 focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm @error('email') ring-2 ring-[#ba1a1a] @enderror"
                                   id="email"
                                   name="email"
                                   type="email"
                                   required
                                   placeholder="utilisateur@domaine.com"
                                   value="{{ old('email') }}"
                                   x-model="email">
                        </div>
                        <p class="text-[11px] leading-4 text-[#434655] flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px] text-[#737686]" aria-hidden="true">info</span>
                            Cet e-mail sert d’identifiant de connexion. Aucun message automatique n’est envoyé ici.
                        </p>
                        @error('email') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-[12px] leading-4 font-semibold text-[#131b2e]" for="phone">
                            Numéro de téléphone
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">smartphone</span>
                            <input class="w-full h-11 pl-10 pr-3.5 bg-white rounded-lg text-[13px] leading-5 text-[#131b2e] placeholder:text-[#737686]/70 focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm @error('phone') ring-2 ring-[#ba1a1a] @enderror"
                                   id="phone"
                                   name="phone"
                                   type="tel"
                                   maxlength="30"
                                   placeholder="Optionnel"
                                   value="{{ old('phone') }}">
                        </div>
                        @error('phone') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-[#89f5e7] flex items-center justify-center text-[#006a61]">
                            <span class="material-symbols-outlined text-[22px]" aria-hidden="true">badge</span>
                        </div>
                        <div>
                            <h2 class="font-display text-[16px] leading-6 font-semibold">Rôle</h2>
                            <p class="text-[11px] leading-4 text-[#434655]">Patient, médecin ou administrateur</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 pt-1">
                    <label class="relative flex flex-col p-4 rounded-xl cursor-pointer transition-all group"
                           :class="role === 'patient' ? 'bg-[#f2f3ff] shadow-sm' : 'bg-white hover:bg-[#f2f3ff]'">
                        <input class="sr-only" name="role" type="radio" value="patient" x-model="role" @checked($oldRole === 'patient')>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[#004ac6] shadow-sm">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">personal_injury</span>
                            </div>
                            <div class="w-5 h-5 rounded-full flex items-center justify-center"
                                 :class="role === 'patient' ? 'bg-[#2563eb] text-white' : 'bg-[#eaedff] text-transparent'">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check</span>
                            </div>
                        </div>
                        <span class="text-[14px] leading-5 font-semibold">Patient</span>
                        <p class="text-[13px] leading-5 text-[#434655] mt-1.5">Accès à l’espace patient et à la prise de rendez-vous.</p>
                    </label>
                    <label class="relative flex flex-col p-4 rounded-xl cursor-pointer transition-all group"
                           :class="role === 'doctor' ? 'bg-[#f2f3ff] shadow-sm' : 'bg-white hover:bg-[#f2f3ff]'">
                        <input class="sr-only" name="role" type="radio" value="doctor" x-model="role" @checked($oldRole === 'doctor')>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-8 h-8 rounded-lg bg-[#f2f3ff] flex items-center justify-center text-[#131b2e]">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">stethoscope</span>
                            </div>
                            <div class="w-5 h-5 rounded-full flex items-center justify-center"
                                 :class="role === 'doctor' ? 'bg-[#2563eb] text-white' : 'bg-[#eaedff] text-transparent'">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check</span>
                            </div>
                        </div>
                        <span class="text-[14px] leading-5 font-semibold">Médecin</span>
                        <p class="text-[13px] leading-5 text-[#434655] mt-1.5">Compte médecin : le profil médical reste à valider ensuite.</p>
                    </label>
                    <label class="relative flex flex-col p-4 rounded-xl cursor-pointer transition-all group"
                           :class="role === 'admin' ? 'bg-[#f2f3ff] shadow-sm' : 'bg-white hover:bg-[#f2f3ff]'">
                        <input class="sr-only" name="role" type="radio" value="admin" x-model="role" @checked($oldRole === 'admin')>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-8 h-8 rounded-lg bg-[#f2f3ff] flex items-center justify-center text-[#131b2e]">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">admin_panel_settings</span>
                            </div>
                            <div class="w-5 h-5 rounded-full flex items-center justify-center"
                                 :class="role === 'admin' ? 'bg-[#2563eb] text-white' : 'bg-[#eaedff] text-transparent'">
                                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check</span>
                            </div>
                        </div>
                        <span class="text-[14px] leading-5 font-semibold">Administrateur</span>
                        <p class="text-[13px] leading-5 text-[#434655] mt-1.5">Accès à la gestion des médecins, spécialités, rendez-vous et utilisateurs.</p>
                    </label>
                </div>
                @error('role') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
            </section>

            <section class="bg-white rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-[#6ffbbe] flex items-center justify-center text-[#006242]">
                            <span class="material-symbols-outlined text-[22px]" aria-hidden="true">lock</span>
                        </div>
                        <div>
                            <h2 class="font-display text-[16px] leading-6 font-semibold">Mot de passe</h2>
                            <p class="text-[11px] leading-4 text-[#434655]">Minimum 8 caractères, confirmation obligatoire</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-1.5">
                        <label class="block text-[12px] leading-4 font-semibold text-[#131b2e]" for="password">
                            Mot de passe <span class="text-[#ba1a1a] font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input class="w-full h-11 pl-3.5 pr-10 bg-white rounded-lg text-[13px] leading-5 text-[#131b2e] placeholder:text-[#737686]/70 focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm @error('password') ring-2 ring-[#ba1a1a] @enderror"
                                   id="password"
                                   name="password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   placeholder="••••••••"
                                   :type="showPassword ? 'text' : 'password'">
                            <button type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#737686] hover:text-[#131b2e]"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        @error('password') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[12px] leading-4 font-semibold text-[#131b2e]" for="password_confirmation">
                            Confirmer le mot de passe <span class="text-[#ba1a1a] font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input class="w-full h-11 pl-3.5 pr-10 bg-white rounded-lg text-[13px] leading-5 text-[#131b2e] placeholder:text-[#737686]/70 focus:outline-none focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   placeholder="••••••••"
                                   :type="showConfirm ? 'text' : 'password'">
                            <button type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#737686] hover:text-[#131b2e]"
                                    @click="showConfirm = !showConfirm"
                                    :aria-label="showConfirm ? 'Masquer la confirmation' : 'Afficher la confirmation'">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true" x-text="showConfirm ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                        <p class="text-[11px] leading-4 text-[#737686] flex items-center gap-1 mt-2">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">sync</span>
                            Les deux mots de passe doivent correspondre.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <aside class="lg:col-span-4 space-y-6">
            <div class="bg-[#f2f3ff] rounded-xl p-4 shadow-sm space-y-2">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-[#004ac6] text-[22px] shrink-0" aria-hidden="true">info</span>
                    <div class="space-y-1">
                        <h3 class="text-[14px] leading-5 font-semibold">Après la création</h3>
                        <p class="text-[13px] leading-5 text-[#434655]">
                            Le compte peut se connecter tout de suite. Un médecin créé ici n’a pas encore de profil médical : il reste à valider depuis Médecins.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm space-y-3">
                <span class="text-[11px] leading-4 uppercase tracking-wider text-[#737686] font-semibold">Aperçu du profil</span>
                <div class="flex items-center gap-3 pt-1">
                    <div class="w-12 h-12 rounded-full bg-[#e2e7ff] flex items-center justify-center text-[#004ac6] font-bold text-lg" x-text="initials()">--</div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[14px] leading-5 font-semibold truncate" x-text="name.trim() || 'Nouvel utilisateur'">Nouvel utilisateur</span>
                        <span class="text-[13px] leading-5 text-[#434655] truncate" x-text="email.trim() || 'utilisateur@domaine.com'">utilisateur@domaine.com</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] leading-4 bg-[#dbe1ff] text-[#00174b] font-semibold" x-text="roleLabel()">Patient</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] leading-4 bg-[#f2f3ff] text-[#434655] font-semibold">Nouveau compte</span>
                </div>
            </div>
        </aside>

        <div class="lg:col-span-12 pt-2 pb-8">
            <div class="bg-white rounded-xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('admin.users.index') }}"
                   class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-[#eaedff] text-[#131b2e] hover:bg-[#e2e7ff] transition-colors text-[14px] leading-5 font-semibold text-center">
                    Annuler
                </a>
                <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                    <button type="submit"
                            name="add_another"
                            value="1"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-white text-[#131b2e] hover:bg-[#f2f3ff] shadow-sm transition-all text-[14px] leading-5 font-medium">
                        Enregistrer et ajouter un autre
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-[#2563eb] text-white font-semibold shadow-md hover:bg-[#004ac6] transition-all flex items-center justify-center gap-2 text-[14px] leading-5">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">person_add</span>
                        <span>Créer l’utilisateur</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
