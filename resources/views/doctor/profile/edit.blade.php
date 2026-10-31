@php
    $completedCount ??= 0;
    $photo = $user->profile_image_url;
    $initials = collect(preg_split('/\s+/', trim((string) $user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

@extends('layouts.doctor')
@section('page-title', 'Mon profil')
@section('page-subtitle', 'Informations professionnelles')

@section('doctor-content')
<div class="sc-stack w-full"
     x-data="{ showPassword: false, showConfirm: false, showCurrent: false, pwdOpen: {{ $errors->updatePassword->isNotEmpty() ? 'true' : 'false' }} }"
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

    @if($user->profile_image)
        <form id="remove-image-form" method="POST" action="{{ route('doctor.profile.removeImage') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <div class="flex flex-col gap-2">
        <nav aria-label="Fil d'Ariane" class="flex items-center gap-1.5 text-[#434655] text-[12px]">
            <span>Espace Praticien</span>
            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
            <span class="text-[#131b2e] font-semibold">Mon profil</span>
        </nav>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h2 class="font-display text-[24px] md:text-[28px] leading-8 md:leading-9 tracking-tight">Mon profil praticien</h2>
                <p class="text-[15px] leading-6 text-[#434655] max-w-3xl">
                    Gérez votre photo, vos coordonnées professionnelles et la présentation de votre cabinet.
                </p>
            </div>
            <button type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-[#f2f3ff] text-[#131b2e] hover:bg-[#e2e7ff] text-[14px] font-semibold shadow-sm self-start lg:self-center"
                    @click="Alpine.$data(document.body).showToast('L’aperçu fiche publique n’est pas géré depuis cet espace.')">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">visibility</span>
                <span>Aperçu fiche publique</span>
            </button>
        </div>

        <div class="p-4 rounded-xl bg-[#f2f3ff] flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg {{ $doctor ? 'bg-[#007d55] text-white' : 'bg-[#ffdad6] text-[#93000a]' }} flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">{{ $doctor ? 'verified_user' : 'hourglass_top' }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[14px] font-bold">{{ $doctor ? 'Profil validé' : 'Profil en attente de validation' }}</span>
                    <span class="text-[13px] text-[#434655]">
                        @if($doctor)
                            Votre fiche est visible pour la prise de rendez-vous en ligne.
                        @else
                            Un administrateur doit encore valider votre profil médical. Vous pouvez déjà mettre à jour votre photo.
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
        @csrf
        @method('PATCH')

        <div class="p-6 rounded-xl bg-white shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-[#dbe1ff]/30 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 relative z-10">
                <div class="relative">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden bg-[#dbe1ff] text-[#004ac6] flex items-center justify-center shadow-md ring-4 ring-[#f2f3ff]">
                        @if($photo)
                            <img id="avatar-preview" src="{{ $photo }}" alt="Photo de Dr. {{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span id="avatar-initials" class="text-3xl font-bold">{{ $initials }}</span>
                            <img id="avatar-preview" src="" alt="" class="w-full h-full object-cover hidden">
                        @endif
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <h3 class="text-[20px] leading-7 font-bold">Dr. {{ $user->name }}</h3>
                    <p class="text-[13px] text-[#434655]">
                        {{ $doctor?->speciality?->name ?? 'Médecin' }}
                    </p>
                    <p class="text-[12px] text-[#737686]">JPEG, PNG, WebP — max 2 Mo</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto relative z-10">
                <label for="profile_image" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-[#004ac6] text-white font-semibold hover:bg-[#003ea8] cursor-pointer shadow-sm">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">photo_camera</span>
                    Changer la photo
                </label>
                <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
                @if($user->profile_image)
                    <button type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-[#f2f3ff] text-[#ba1a1a] hover:bg-[#ffdad6] font-semibold"
                            onclick="confirm('Supprimer la photo de profil ?') && document.getElementById('remove-image-form').submit()">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>
                        Supprimer
                    </button>
                @endif
            </div>
        </div>
        @error('profile_image')<p class="text-[#ba1a1a] text-sm -mt-4">{{ $message }}</p>@enderror

        @if($doctor)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-white shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#f2f3ff] flex items-center justify-center text-[#004ac6]">
                        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">stethoscope</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[16px] leading-6 font-bold truncate">{{ $doctor->speciality->name }}</span>
                        <span class="text-[13px] text-[#434655]">Spécialité (gérée par l’administrateur)</span>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-white shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#f2f3ff] flex items-center justify-center text-[#007d55]">
                        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">task_alt</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[16px] leading-6 font-bold">{{ $completedCount }}</span>
                        <span class="text-[13px] text-[#434655]">Consultations terminées</span>
                    </div>
                </div>
                <div class="p-4 rounded-xl bg-white shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#f2f3ff] flex items-center justify-center text-[#006a61]">
                        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">badge</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-[16px] leading-6 font-bold truncate">#{{ $doctor->id }}</span>
                        <span class="text-[13px] text-[#434655]">Référence interne</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-8 flex flex-col gap-6">
                <div class="p-6 rounded-xl bg-white shadow-sm flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#f2f3ff] flex items-center justify-center text-[#004ac6]">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">person</span>
                        </span>
                        <div>
                            <h3 class="text-[16px] leading-6 font-bold">Informations du compte</h3>
                            <p class="text-[13px] text-[#434655]">Le nom et l’e-mail viennent de votre compte. Ils ne sont pas modifiables ici.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-semibold" for="display_name">Nom</label>
                            <input id="display_name" class="w-full h-11 px-3 rounded-lg bg-[#f2f3ff] text-[#434655]" type="text" value="{{ $user->name }}" readonly>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[12px] font-semibold" for="display_email">E-mail</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">mail</span>
                                <input id="display_email" class="w-full h-11 pl-9 pr-3 rounded-lg bg-[#f2f3ff] text-[#434655]" type="email" value="{{ $user->email }}" readonly>
                            </div>
                        </div>
                        @if($doctor)
                            <div class="flex flex-col gap-1.5 md:col-span-2">
                                <label class="text-[12px] font-semibold" for="phone">Téléphone</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">phone_iphone</span>
                                    <input class="w-full h-11 pl-9 pr-3 rounded-lg bg-white shadow-sm outline-none ring-1 ring-[#c3c6d7] focus:ring-2 focus:ring-[#2563eb]"
                                           id="phone" name="phone" type="tel" value="{{ old('phone', $doctor->phone) }}">
                                </div>
                                @error('phone')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                            </div>
                        @endif
                    </div>
                </div>

                @if($doctor)
                    <div class="p-6 rounded-xl bg-white shadow-sm flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-[#f2f3ff] flex items-center justify-center text-[#004ac6]">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">stethoscope</span>
                            </span>
                            <div>
                                <h3 class="text-[16px] leading-6 font-bold">Informations professionnelles</h3>
                                <p class="text-[13px] text-[#434655]">Cabinet, présentation et coordonnées visibles côté réservation.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5 md:col-span-2">
                                <label class="text-[12px] font-semibold">Spécialité médicale</label>
                                <input class="w-full h-11 px-3 rounded-lg bg-[#f2f3ff] text-[#434655]" type="text" value="{{ $doctor->speciality->name }}" readonly>
                                <p class="text-[11px] text-[#737686]">La spécialité est gérée par l’administrateur.</p>
                            </div>
                            <div class="flex flex-col gap-1.5 md:col-span-2">
                                <label class="text-[12px] font-semibold" for="address">Adresse du cabinet</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">location_on</span>
                                    <input class="w-full h-11 pl-9 pr-3 rounded-lg bg-white shadow-sm outline-none ring-1 ring-[#c3c6d7] focus:ring-2 focus:ring-[#2563eb]"
                                           id="address" name="address" type="text" value="{{ old('address', $doctor->address) }}">
                                </div>
                                @error('address')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex flex-col gap-1.5 md:col-span-2">
                                <label class="text-[12px] font-semibold" for="bio">Biographie</label>
                                <textarea class="w-full p-3 rounded-lg bg-white shadow-sm outline-none ring-1 ring-[#c3c6d7] focus:ring-2 focus:ring-[#2563eb] leading-relaxed"
                                          id="bio" name="bio" rows="5">{{ old('bio', $doctor->bio) }}</textarea>
                                @error('bio')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2 p-3.5 rounded-xl bg-[#f2f3ff]">
                                <span class="text-[14px] font-semibold flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-[#004ac6]" aria-hidden="true">domain</span>
                                    Consultation au cabinet
                                </span>
                                <p class="text-[13px] text-[#434655] mt-1">Consultation en présentiel uniquement.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="xl:col-span-4 flex flex-col gap-6">
                <div class="p-6 rounded-xl bg-white shadow-sm flex flex-col gap-3">
                    <h3 class="text-[16px] leading-6 font-bold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[#004ac6] text-[20px]" aria-hidden="true">pin_drop</span>
                        Localisation du cabinet
                    </h3>
                    @if($doctor?->address)
                        <div class="p-3 rounded-xl bg-[#f2f3ff] flex items-start gap-2">
                            <span class="material-symbols-outlined text-[#ba1a1a] text-[18px]" aria-hidden="true">location_on</span>
                            <span class="text-[13px] font-semibold">{{ $doctor->address }}</span>
                        </div>
                    @else
                        <p class="text-[13px] text-[#434655]">Aucune adresse de cabinet n’est enregistrée.</p>
                    @endif
                    <a href="{{ route('doctor.availabilities.index') }}" class="text-[12px] font-semibold text-[#004ac6] hover:underline">
                        Gérer mes disponibilités
                    </a>
                </div>
            </div>
        </div>

        <div class="sticky bottom-4 z-30 p-4 rounded-2xl bg-white/90 backdrop-blur-xl shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 ring-1 ring-[#c3c6d7]/30">
            <span class="text-[13px] text-[#434655]">
                Les champs enregistrés ici sont utilisés pour la prise de rendez-vous en ligne.
            </span>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#2563eb] text-white font-semibold hover:bg-[#004ac6] shadow-md inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">save</span>
                Enregistrer les modifications
            </button>
        </div>
    </form>

    <div class="p-6 rounded-xl bg-white shadow-sm flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-[#f2f3ff] flex items-center justify-center text-[#004ac6]">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">security</span>
            </span>
            <div>
                <h3 class="text-[16px] leading-6 font-bold">Sécurité du compte</h3>
                <p class="text-[13px] text-[#434655]">Changez votre mot de passe. Laissez vide si vous ne souhaitez pas le modifier.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-[#f2f3ff] flex flex-col justify-between gap-3">
                <div>
                    <span class="text-[14px] font-semibold">Authentification</span>
                    <p class="text-[13px] text-[#434655] mt-1">La connexion se fait par e-mail et mot de passe.</p>
                </div>
                <button type="button" class="self-start text-[#004ac6] text-[12px] font-semibold hover:underline"
                        @click="Alpine.$data(document.body).showToast('La double authentification n’est pas gérée.')">
                    Option non configurée
                </button>
            </div>
            <div class="p-4 rounded-xl bg-[#f2f3ff] flex flex-col justify-between gap-3">
                <span class="text-[14px] font-semibold">Mot de passe de connexion</span>
                <button type="button"
                        class="self-start inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white text-[#131b2e] hover:bg-[#e2e7ff] text-[12px] font-semibold shadow-sm"
                        @click="pwdOpen = !pwdOpen">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">key</span>
                    Modifier le mot de passe
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('password.update') }}" class="p-4 rounded-xl bg-[#e2e7ff]/40 flex flex-col gap-4" x-show="pwdOpen" x-cloak>
            @csrf
            @method('PUT')
            <h4 class="text-[14px] font-bold">Changement de mot de passe</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] text-[#434655] font-semibold" for="current_password">Mot de passe actuel</label>
                    <input class="h-10 px-3 rounded-lg bg-white outline-none ring-1 ring-[#c3c6d7] focus:ring-2 focus:ring-[#2563eb]"
                           id="current_password" name="current_password" :type="showCurrent ? 'text' : 'password'" autocomplete="current-password">
                    @error('current_password', 'updatePassword')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] text-[#434655] font-semibold" for="password">Nouveau mot de passe</label>
                    <input class="h-10 px-3 rounded-lg bg-white outline-none ring-1 ring-[#c3c6d7] focus:ring-2 focus:ring-[#2563eb]"
                           id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="8 caractères minimum">
                    @error('password', 'updatePassword')<p class="text-[#ba1a1a] text-sm">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[11px] text-[#434655] font-semibold" for="password_confirmation">Confirmer</label>
                    <input class="h-10 px-3 rounded-lg bg-white outline-none ring-1 ring-[#c3c6d7] focus:ring-2 focus:ring-[#2563eb]"
                           id="password_confirmation" name="password_confirmation" :type="showConfirm ? 'text' : 'password'" autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="self-start px-4 py-2 rounded-lg bg-[#2563eb] text-white text-[12px] font-semibold">
                Mettre à jour le mot de passe
            </button>
        </form>
    </div>
</div>
@endsection
