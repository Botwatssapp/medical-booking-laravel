@extends('layouts.admin')
@section('page-title', 'Modifier l’utilisateur')
@section('page-subtitle', $user->email)

@php
    $appointmentCount ??= 0;
    $oldRole = old('role', $user->role);
    $isPendingDoctor = $user->role === 'doctor' && ! $user->doctor;
    $isValidatedDoctor = $user->role === 'doctor' && (bool) $user->doctor;
    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? 'U', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    };
    $roleLabel = match ($user->role) {
        'admin' => 'Administrateur',
        'doctor' => 'Médecin',
        default => 'Patient',
    };
@endphp

@section('admin-content')
<div class="sc-stack w-full"
     x-data="{
        role: @js($oldRole),
        showPassword: false,
        showConfirm: false
     }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <nav aria-label="Fil d'Ariane" class="flex items-center gap-1 text-[12px] leading-4 text-[#434655]">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6] transition-colors">Admin</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <a href="{{ route('admin.users.index') }}" class="hover:text-[#004ac6] transition-colors">Utilisateurs</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <span class="text-[#131b2e] font-semibold">Modifier</span>
        </nav>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-1 text-[#004ac6] text-[14px] leading-5 font-semibold hover:underline group">
            <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform" aria-hidden="true">arrow_back</span>
            <span>Retour aux utilisateurs</span>
        </a>
    </div>

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-[28px] leading-9 font-bold tracking-tight">Modifier l’utilisateur</h1>
            <p class="text-[15px] leading-6 text-[#434655] mt-1">
                Modifiez les informations personnelles, le rôle et le mot de passe de ce compte.
            </p>
        </div>
        <div class="flex items-center gap-2 self-start md:self-auto px-3 py-1.5 rounded-full bg-[#e2e7ff] text-[#131b2e] text-[11px] leading-4">
            <span class="w-2 h-2 rounded-full bg-[#006242]"></span>
            <span>Compte #{{ $user->id }}</span>
        </div>
    </div>

    <section class="bg-white rounded-xl p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="relative">
                    @if($user->profile_image_url)
                        <img src="{{ $user->profile_image_url }}"
                             alt="Photo de {{ $user->name }}"
                             class="w-20 h-20 rounded-full object-cover shadow-sm bg-[#eaedff]">
                    @else
                        <div class="w-20 h-20 rounded-full bg-[#e2e7ff] text-[#004ac6] flex items-center justify-center text-xl font-bold shadow-sm">
                            {{ $initials($user->name) }}
                        </div>
                    @endif
                </div>
                <div class="flex flex-col">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-display text-[20px] leading-7 font-bold">{{ $user->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#86f2e4]/50 text-[#006f66] text-[11px] leading-4 font-semibold">
                            {{ $roleLabel }}
                        </span>
                        @if($isPendingDoctor)
                            <span class="px-2.5 py-0.5 rounded-full bg-[#dae2fd] text-[#434655] text-[11px] leading-4 font-medium">En attente</span>
                        @elseif($isValidatedDoctor)
                            <span class="px-2.5 py-0.5 rounded-full bg-[#007d55] text-white text-[11px] leading-4 font-medium">Profil validé</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-[#f2f3ff] text-[#434655] text-[11px] leading-4 font-medium">Inscrit</span>
                        @endif
                    </div>
                    <p class="text-[13px] leading-5 text-[#434655] mt-1 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">mail</span>
                        <span>{{ $user->email }}</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-[#434655] text-[11px] leading-4 mt-2">
                        <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">event_available</span>
                        <span>
                            Inscrit le {{ $user->created_at?->locale('fr')->translatedFormat('d M Y') }}
                            @if($user->isPatient())
                                · {{ $appointmentCount }} rendez-vous
                            @elseif($isValidatedDoctor && $user->doctor?->speciality)
                                · {{ $user->doctor->speciality->name }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4 bg-[#f2f3ff] p-4 rounded-lg">
                @if($user->phone)
                    <div class="flex flex-col">
                        <span class="text-[11px] leading-4 text-[#737686] uppercase tracking-wider">Téléphone</span>
                        <span class="text-[14px] leading-5 font-semibold mt-0.5">{{ $user->phone }}</span>
                    </div>
                    <div class="w-px h-8 bg-[#c3c6d7] hidden sm:block"></div>
                @endif
                <div class="flex flex-col">
                    <span class="text-[11px] leading-4 text-[#737686] uppercase tracking-wider">E-mail</span>
                    <span class="text-[14px] leading-5 font-semibold mt-0.5">
                        {{ $user->email_verified_at ? 'Vérifié le '.$user->email_verified_at->format('d/m/Y') : 'Non vérifié' }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <section class="bg-white rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-2">
                <div class="w-8 h-8 rounded-lg bg-[#e2e7ff] flex items-center justify-center text-[#004ac6]">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
                </div>
                <div>
                    <h3 class="font-display text-[16px] leading-6 font-semibold">Informations personnelles</h3>
                    <p class="text-[13px] leading-5 text-[#434655]">Nom, e-mail et téléphone enregistrés sur le compte.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="text-[14px] leading-5 font-semibold" for="name">Nom</label>
                    <input class="h-[42px] px-3 rounded-lg bg-[#f2f3ff] text-[15px] leading-6 focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 @error('name') ring-2 ring-[#ba1a1a] @enderror"
                           id="name"
                           name="name"
                           type="text"
                           required
                           maxlength="255"
                           value="{{ old('name', $user->name) }}">
                    @error('name') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[14px] leading-5 font-semibold" for="phone">Téléphone</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">call</span>
                        <input class="w-full h-[42px] pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[15px] leading-6 focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 @error('phone') ring-2 ring-[#ba1a1a] @enderror"
                               id="phone"
                               name="phone"
                               type="tel"
                               maxlength="30"
                               value="{{ old('phone', $user->phone) }}">
                    </div>
                    @error('phone') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-1.5 md:col-span-2 lg:col-span-3">
                    <label class="text-[14px] leading-5 font-semibold" for="email">Adresse e-mail <span class="text-[#ba1a1a]">*</span></label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">mail</span>
                        <input class="w-full h-[42px] pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[15px] leading-6 focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 @error('email') ring-2 ring-[#ba1a1a] @enderror"
                               id="email"
                               name="email"
                               type="email"
                               required
                               value="{{ old('email', $user->email) }}">
                    </div>
                    @error('email') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-2">
                <div class="w-8 h-8 rounded-lg bg-[#e2e7ff] flex items-center justify-center text-[#004ac6]">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">admin_panel_settings</span>
                </div>
                <div>
                    <h3 class="font-display text-[16px] leading-6 font-semibold">Rôle</h3>
                    <p class="text-[13px] leading-5 text-[#434655]">Définit l’interface accessible à la prochaine connexion.</p>
                </div>
            </div>
            <div class="flex items-start gap-2 p-4 rounded-lg bg-[#ffdad6]/40">
                <span class="material-symbols-outlined text-[#ba1a1a] text-[22px] shrink-0 mt-0.5" aria-hidden="true">warning</span>
                <p class="text-[13px] leading-5">
                    <strong class="font-semibold text-[#ba1a1a]">Attention :</strong> changer le rôle change l’espace affiché après connexion. Un médecin sans profil reste à valider.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                <label class="relative flex flex-col justify-between p-4 rounded-xl cursor-pointer transition-all"
                       :class="role === 'patient' ? 'bg-[#f2f3ff] shadow-sm' : 'bg-white hover:bg-[#f2f3ff]'">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6] text-[24px]" aria-hidden="true">personal_injury</span>
                            <span class="font-display text-[16px] leading-6 font-semibold">Patient</span>
                        </div>
                        <input class="w-4 h-4 accent-[#004ac6]" name="role" type="radio" value="patient" x-model="role" @checked($oldRole === 'patient')>
                    </div>
                    <p class="text-[13px] leading-5 text-[#434655] mt-3">Prend des rendez-vous et consulte son espace patient.</p>
                </label>
                <label class="relative flex flex-col justify-between p-4 rounded-xl cursor-pointer transition-all"
                       :class="role === 'doctor' ? 'bg-[#f2f3ff] shadow-sm' : 'bg-white hover:bg-[#f2f3ff]'">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#006a61] text-[24px]" aria-hidden="true">stethoscope</span>
                            <span class="font-display text-[16px] leading-6 font-semibold">Médecin</span>
                        </div>
                        <input class="w-4 h-4 accent-[#004ac6]" name="role" type="radio" value="doctor" x-model="role" @checked($oldRole === 'doctor')>
                    </div>
                    <p class="text-[13px] leading-5 text-[#434655] mt-3">Compte médecin : le profil médical se valide ensuite depuis Médecins.</p>
                </label>
                <label class="relative flex flex-col justify-between p-4 rounded-xl cursor-pointer transition-all"
                       :class="role === 'admin' ? 'bg-[#f2f3ff] shadow-sm' : 'bg-white hover:bg-[#f2f3ff]'">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#004ac6] text-[24px]" aria-hidden="true">shield_person</span>
                            <span class="font-display text-[16px] leading-6 font-semibold">Administrateur</span>
                        </div>
                        <input class="w-4 h-4 accent-[#004ac6]" name="role" type="radio" value="admin" x-model="role" @checked($oldRole === 'admin')>
                    </div>
                    <p class="text-[13px] leading-5 text-[#434655] mt-3">Accès à la gestion des médecins, spécialités, rendez-vous et utilisateurs.</p>
                </label>
            </div>
            @error('role') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
        </section>

        <section class="bg-white rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-2">
                <div class="w-8 h-8 rounded-lg bg-[#e2e7ff] flex items-center justify-center text-[#004ac6]">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">lock_reset</span>
                </div>
                <div>
                    <h3 class="font-display text-[16px] leading-6 font-semibold">Modifier le mot de passe</h3>
                    <p class="text-[13px] leading-5 text-[#434655]">Laissez vide si vous ne souhaitez pas changer le mot de passe actuel.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <div class="flex flex-col gap-1.5">
                    <label class="text-[14px] leading-5 font-semibold" for="password">Nouveau mot de passe</label>
                    <div class="relative">
                        <input class="w-full h-[42px] pl-3 pr-10 rounded-lg bg-[#f2f3ff] text-[15px] leading-6 focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 @error('password') ring-2 ring-[#ba1a1a] @enderror"
                               id="password"
                               name="password"
                               minlength="8"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               :type="showPassword ? 'text' : 'password'">
                        <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#737686] hover:text-[#131b2e]"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    @error('password') <p class="text-[11px] text-[#ba1a1a]">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[14px] leading-5 font-semibold" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                    <div class="relative">
                        <input class="w-full h-[42px] pl-3 pr-10 rounded-lg bg-[#f2f3ff] text-[15px] leading-6 focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20"
                               id="password_confirmation"
                               name="password_confirmation"
                               minlength="8"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               :type="showConfirm ? 'text' : 'password'">
                        <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#737686] hover:text-[#131b2e]"
                                @click="showConfirm = !showConfirm"
                                :aria-label="showConfirm ? 'Masquer la confirmation' : 'Afficher la confirmation'">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true" x-text="showConfirm ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                </div>
            </div>
            <p class="flex items-center gap-2 pt-1 text-[#434655] text-[13px] leading-5">
                <span class="material-symbols-outlined text-[16px] text-[#737686] shrink-0" aria-hidden="true">info</span>
                <span>Minimum 8 caractères si vous changez le mot de passe.</span>
            </p>
        </section>

        <div class="bg-white rounded-xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-start">
                @if($user->id !== auth()->id())
                    <button type="submit"
                            form="delete-user-form"
                            class="px-3 py-2 rounded-lg text-[#ba1a1a] hover:bg-[#ffdad6] text-[14px] leading-5 font-semibold transition-colors flex items-center gap-1.5"
                            onclick="return confirm('Supprimer le compte de {{ addslashes($user->name) }} ?')">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>
                        <span>Supprimer le compte</span>
                    </button>
                @endif
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2.5 rounded-lg bg-[#f2f3ff] text-[#131b2e] hover:bg-[#e2e7ff] text-[14px] leading-5 font-semibold transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-[#2563eb] text-white text-[14px] leading-5 font-semibold shadow-sm hover:bg-[#004ac6] transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">save</span>
                    <span>Enregistrer les modifications</span>
                </button>
            </div>
        </div>
    </form>

    @if($user->id !== auth()->id())
        <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>
@endsection
