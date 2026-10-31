@extends('layouts.app')
@section('title', 'Mon profil')
@section('suppress-global-flash', true)

@php
    $completedAppointments ??= 0;
    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? 'P', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    };
    $passwordStatus = session('status') === 'password-updated'
        ? 'Mot de passe mis à jour.'
        : session('status');
@endphp

@section('content')
<div class="bg-[#faf8ff] text-[#131b2e] min-h-screen"
     x-data="{ showPassword: false, showConfirm: false, showCurrent: false }">
    <div class="sc-page max-w-[1080px]">
        @if(session('success') || $passwordStatus || $errors->any())
            <div class="mb-6 space-y-3" data-sc-flash>
                @if(session('success'))
                    <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-[#6ffbbe] text-[#002113] shadow-sm" role="status">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-[#006242] flex items-center justify-center shrink-0 text-white shadow-sm">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">check_circle</span>
                            </div>
                            <p class="font-display text-[16px] leading-6 truncate">{{ session('success') }}</p>
                        </div>
                        <button class="p-1 rounded-lg hover:bg-[#4edea3]/40" type="button" onclick="this.closest('[role=status]').remove()" title="Fermer la notification">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
                        </button>
                    </div>
                @endif
                @if($passwordStatus)
                    <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-[#6ffbbe] text-[#002113] shadow-sm" role="status">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-[#006242] flex items-center justify-center shrink-0 text-white shadow-sm">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">check_circle</span>
                            </div>
                            <p class="font-display text-[16px] leading-6 truncate">{{ $passwordStatus }}</p>
                        </div>
                    </div>
                @endif
                @if($errors->any())
                    <div class="p-4 rounded-xl bg-[#ffdad6] text-[#93000a]" role="alert">
                        <p class="font-semibold mb-1">Veuillez corriger les champs indiqués.</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <nav aria-label="Fil d'Ariane" class="flex items-center gap-1 text-[12px] leading-4 text-[#434655] mb-1">
                    <a class="hover:text-[#004ac6] transition-colors" href="{{ route('patient.dashboard') }}">Accueil</a>
                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
                    <span class="text-[#131b2e] font-semibold">Mon profil</span>
                </nav>
                <h1 class="font-display text-[32px] md:text-[40px] leading-10 md:leading-[48px] tracking-tight">Mon profil</h1>
                <p class="text-[18px] leading-7 text-[#434655] max-w-2xl mt-1">
                    Gérez vos informations personnelles, vos coordonnées de contact et la sécurité de votre compte.
                </p>
            </div>
            <div class="flex items-center gap-1 bg-[#e2e7ff] px-4 py-2 rounded-xl">
                <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">verified_user</span>
                <div class="flex flex-col">
                    <span class="text-[11px] leading-4 text-[#434655]">Compte patient</span>
                    <span class="text-[12px] leading-4 text-[#131b2e]">Espace connecté</span>
                </div>
            </div>
        </div>

        @if($user->profile_image)
            <form id="remove-image-form" method="POST" action="{{ route('patient.profile.removeImage') }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

        <section class="bg-white rounded-xl shadow-sm p-6 mb-8 relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-[#004ac6]/5 rounded-full pointer-events-none blur-2xl"></div>
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full bg-[#e2e7ff] p-1 shadow-inner overflow-hidden">
                            @if($user->profile_image_url)
                                <img id="avatar-preview" src="{{ $user->profile_image_url }}"
                                     alt="Photo de profil de {{ $user->name }}"
                                     class="w-full h-full rounded-full object-cover">
                            @else
                                <span id="avatar-initials" class="w-full h-full rounded-full flex items-center justify-center text-2xl font-bold text-[#004ac6]">
                                    {{ $initials($user->name) }}
                                </span>
                                <img id="avatar-preview" src="" alt="" class="w-full h-full rounded-full object-cover hidden">
                            @endif
                        </div>
                        <label class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#004ac6] text-white flex items-center justify-center cursor-pointer shadow-md hover:bg-[#004ac6]/90"
                               for="profile_image"
                               title="Modifier la photo">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">photo_camera</span>
                        </label>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="font-display text-[28px] leading-9">{{ $user->name }}</h2>
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#6ffbbe] text-[#002113] text-[11px] leading-4 font-semibold">
                                    <span class="material-symbols-outlined text-[14px]" aria-hidden="true">check</span> Email vérifié
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#e2e7ff] text-[#434655] text-[11px] leading-4 font-semibold">
                                    Non vérifié
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 text-[#434655] text-[13px] leading-5 mt-1 flex-wrap">
                            <span class="material-symbols-outlined text-[16px] text-[#004ac6]" aria-hidden="true">mail</span>
                            <span>{{ $user->email }}</span>
                            <span class="text-[#c3c6d7]">•</span>
                            <span class="text-[11px] leading-4 bg-[#e2e7ff] text-[#131b2e] px-2 py-0.5 rounded">Compte #{{ $user->id }}</span>
                        </div>
                        <p class="text-[13px] leading-5 text-[#434655] opacity-80 mt-1">JPG, PNG ou WEBP. 2 Mo maximum.</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row md:flex-col items-start md:items-end gap-2 w-full md:w-auto">
                    <div class="flex items-center gap-1">
                        <label for="profile_image" class="px-4 py-1 rounded-lg bg-[#f2f3ff] hover:bg-[#eaedff] text-[#131b2e] text-[12px] leading-4 transition-colors shadow-sm cursor-pointer">
                            Changer l'image
                        </label>
                        @if($user->profile_image)
                            <button class="p-1 text-[#ba1a1a] hover:bg-[#ffdad6]/40 rounded-lg transition-colors"
                                    title="Supprimer la photo"
                                    type="button"
                                    onclick="confirm('Supprimer la photo de profil ?') && document.getElementById('remove-image-form').submit()">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">delete</span>
                            </button>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-1 mt-1">
                        <span class="px-2 py-1 bg-[#eaedff] text-[#434655] rounded-full text-[11px] leading-4">
                            Membre depuis {{ $user->created_at?->locale('fr')->translatedFormat('M Y') }}
                        </span>
                        <span class="px-2 py-1 bg-[#dbe1ff] text-[#00174b] rounded-full text-[11px] leading-4 font-medium">
                            {{ $completedAppointments }} consultation{{ $completedAppointments > 1 ? 's' : '' }} honorée{{ $completedAppointments > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <form method="POST"
              action="{{ route('patient.profile.update') }}"
              enctype="multipart/form-data"
              class="space-y-8">
            @csrf
            @method('PATCH')
            <input type="file" id="profile_image" name="profile_image"
                   accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">

            <section class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-[#dbe1ff] text-[#00174b] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]" aria-hidden="true">badge</span>
                    </div>
                    <div>
                        <h2 class="font-display text-[20px] leading-7">Informations personnelles</h2>
                        <p class="text-[15px] leading-6 text-[#434655]">
                            Vos coordonnées utilisées pour vos rendez-vous.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e] flex items-center gap-1" for="name">
                            Nom <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="name"
                               name="name"
                               required
                               type="text"
                               value="{{ old('name', $user->name) }}">
                        @error('name') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e] flex items-center gap-1" for="email">
                            Adresse e-mail <span class="text-[#ba1a1a]">*</span>
                        </label>
                        <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="email"
                               name="email"
                               required
                               type="email"
                               value="{{ old('email', $user->email) }}">
                        <span class="text-[13px] leading-5 text-[#434655]">Identifiant de connexion de ce compte.</span>
                        @error('email') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="phone">Numéro de téléphone</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-[#434655] pointer-events-none material-symbols-outlined text-[18px]" aria-hidden="true">smartphone</span>
                            <input class="w-full h-11 pl-10 pr-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                   id="phone"
                                   name="phone"
                                   type="tel"
                                   value="{{ old('phone', $user->phone) }}">
                        </div>
                        @error('phone') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="birth_date">Date de naissance</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-[#434655] pointer-events-none material-symbols-outlined text-[18px]" aria-hidden="true">calendar_today</span>
                            <input class="w-full h-11 pl-10 pr-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                   id="birth_date"
                                   name="birth_date"
                                   type="date"
                                   max="{{ now()->subDay()->format('Y-m-d') }}"
                                   value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                        </div>
                        @error('birth_date') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="gender">Sexe</label>
                        <x-ui.select name="gender" id="gender" label="Sexe">
                            <option value="">— Non renseigné —</option>
                            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Homme</option>
                            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Femme</option>
                            <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Autre</option>
                        </x-ui.select>
                        @error('gender') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2 flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="address">Adresse</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-[#434655] pointer-events-none material-symbols-outlined text-[18px]" aria-hidden="true">home_pin</span>
                            <input class="w-full h-11 pl-10 pr-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                                   id="address"
                                   name="address"
                                   type="text"
                                   placeholder="Rue, quartier, numéro de bâtiment..."
                                   value="{{ old('address', $user->address) }}">
                        </div>
                        @error('address') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="blood_type">Groupe sanguin</label>
                        <x-ui.select name="blood_type" id="blood_type" label="Groupe sanguin">
                            <option value="">— Non renseigné —</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                <option value="{{ $bt }}" {{ old('blood_type', $user->blood_type) === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="weight">Poids (kg)</label>
                        <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="weight"
                               name="weight"
                               type="number"
                               step="0.1"
                               min="1"
                               max="500"
                               value="{{ old('weight', $user->weight ? rtrim(rtrim((string) $user->weight, '0'), '.') : '') }}">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="height">Taille (cm)</label>
                        <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="height"
                               name="height"
                               type="number"
                               step="0.1"
                               min="30"
                               max="300"
                               value="{{ old('height', $user->height ? rtrim(rtrim((string) $user->height, '0'), '.') : '') }}">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[14px] leading-5 text-[#131b2e]" for="emergency_contact">Contact d'urgence</label>
                        <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="emergency_contact"
                               name="emergency_contact"
                               type="text"
                               value="{{ old('emergency_contact', $user->emergency_contact) }}">
                    </div>
                </div>
                <div class="mt-8 pt-6 flex flex-col sm:flex-row items-center justify-end gap-4">
                    <a class="w-full sm:w-auto px-6 py-2 rounded-lg bg-[#e2e7ff] hover:bg-[#eaedff] text-[#131b2e] text-[14px] leading-5 text-center transition-colors shadow-sm"
                       href="{{ route('patient.dashboard') }}">
                        Annuler
                    </a>
                    <button class="w-full sm:w-auto px-8 py-2 rounded-lg bg-[#004ac6] hover:bg-[#004ac6]/90 text-white text-[14px] leading-5 flex items-center justify-center gap-1 shadow-md"
                            type="submit">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">save</span>
                        Enregistrer les modifications
                    </button>
                </div>
            </section>
        </form>

        <section class="bg-white rounded-xl shadow-sm p-6 sm:p-8 mt-8">
            <div class="flex items-start gap-4 mb-6">
                <div class="w-10 h-10 rounded-xl bg-[#86f2e4] text-[#006f66] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">shield_person</span>
                </div>
                <div>
                    <h2 class="font-display text-[20px] leading-7">Sécurité du compte</h2>
                    <p class="text-[15px] leading-6 text-[#434655]">
                        Laissez vide si vous ne souhaitez pas changer le mot de passe.
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('password.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf
                @method('PUT')
                <div class="md:col-span-2 flex flex-col gap-1">
                    <label class="text-[14px] leading-5 text-[#131b2e]" for="current_password">Mot de passe actuel</label>
                    <div class="relative flex items-center">
                        <input class="w-full h-11 px-4 pr-10 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="current_password"
                               name="current_password"
                               :type="showCurrent ? 'text' : 'password'"
                               autocomplete="current-password">
                        <button class="absolute right-3 text-[#434655] hover:text-[#131b2e]" type="button" @click="showCurrent = !showCurrent">
                            <span class="material-symbols-outlined text-[20px]" x-text="showCurrent ? 'visibility_off' : 'visibility'" aria-hidden="true">visibility</span>
                        </button>
                    </div>
                    @error('current_password', 'updatePassword') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[14px] leading-5 text-[#131b2e]" for="password">Nouveau mot de passe</label>
                    <div class="relative flex items-center">
                        <input class="w-full h-11 px-4 pr-10 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="password"
                               name="password"
                               :type="showPassword ? 'text' : 'password'"
                               autocomplete="new-password"
                               placeholder="••••••••••••">
                        <button class="absolute right-3 text-[#434655] hover:text-[#131b2e]" type="button" @click="showPassword = !showPassword">
                            <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'" aria-hidden="true">visibility</span>
                        </button>
                    </div>
                    @error('password', 'updatePassword') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[14px] leading-5 text-[#131b2e]" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                    <div class="relative flex items-center">
                        <input class="w-full h-11 px-4 pr-10 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 outline-none focus:bg-white focus:ring-2 focus:ring-[#004ac6] shadow-sm"
                               id="password_confirmation"
                               name="password_confirmation"
                               :type="showConfirm ? 'text' : 'password'"
                               autocomplete="new-password"
                               placeholder="••••••••••••">
                        <button class="absolute right-3 text-[#434655] hover:text-[#131b2e]" type="button" @click="showConfirm = !showConfirm">
                            <span class="material-symbols-outlined text-[20px]" x-text="showConfirm ? 'visibility_off' : 'visibility'" aria-hidden="true">visibility</span>
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-4 rounded-xl bg-[#f2f3ff]/80">
                    <span class="flex items-center gap-1 text-[13px] leading-5 text-[#434655]">
                        <span class="material-symbols-outlined text-[16px] text-[#006242]" aria-hidden="true">check_circle</span>
                        8 caractères minimum
                    </span>
                    <button class="self-start sm:self-auto px-4 py-1 rounded-lg bg-[#eaedff] text-[#131b2e] text-[12px] leading-4 hover:bg-[#e2e7ff] transition-colors"
                            type="submit">
                        Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
document.getElementById('profile_image')?.addEventListener('change', function () {
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
</script>
@endsection
