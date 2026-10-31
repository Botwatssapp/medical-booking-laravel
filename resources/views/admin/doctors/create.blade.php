@extends('layouts.admin')
@section('page-title', request('user_id') ? 'Confirmer le compte médecin' : 'Valider / Ajouter un médecin')
@section('page-subtitle', 'La création du profil médical rend le médecin réservable')

@php
    $pendingUsers = $users->map(fn ($user) => [
        'id' => (string) $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ])->values();
    $specialtyOptions = $specialties->map(fn ($specialty) => [
        'id' => (string) $specialty->id,
        'name' => $specialty->name,
    ])->values();
    $defaultUserId = (string) old('user_id', request('user_id', $users->first()?->id));
    if ($defaultUserId !== '' && ! $users->contains('id', (int) $defaultUserId)) {
        $defaultUserId = (string) ($users->first()?->id ?? '');
    }
@endphp

@section('admin-content')
<div class="max-w-7xl mx-auto w-full"
     x-data="{
        userId: @js($defaultUserId),
        specialityId: @js((string) old('speciality_id', '')),
        users: @js($pendingUsers),
        specialties: @js($specialtyOptions),
        get selectedUser() {
            return this.users.find((user) => String(user.id) === String(this.userId)) || null;
        },
        get selectedSpecialty() {
            return this.specialties.find((item) => String(item.id) === String(this.specialityId)) || null;
        },
        initials(name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '--';
            const first = parts[0].charAt(0);
            const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : '';
            return (first + last).toUpperCase();
        }
     }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
        <nav aria-label="Fil d'Ariane" class="flex items-center gap-1 text-[12px] leading-4 text-[#434655]">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6] transition-colors">Admin</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <a href="{{ route('admin.doctors.index') }}" class="hover:text-[#004ac6] transition-colors">Médecins</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <span class="text-[#004ac6] font-semibold">Valider &amp; Ajouter</span>
        </nav>
        <a href="{{ route('admin.doctors.index') }}"
           class="inline-flex items-center gap-1 text-[#004ac6] hover:text-[#003ea8] transition-colors text-[12px] leading-4 font-semibold self-start md:self-auto">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            <span>Retour aux praticiens</span>
        </a>
    </div>

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-1 mb-1">
                <span class="px-2 py-0.5 rounded-full bg-[#86f2e4]/70 text-[#006f66] text-[11px] leading-4 font-semibold">
                    Validation du profil
                </span>
            </div>
            <h1 class="font-display text-[28px] leading-9 text-[#131b2e] tracking-tight">
                Valider / Ajouter un médecin
            </h1>
            <p class="text-[15px] leading-6 text-[#434655] mt-1 max-w-2xl">
                Choisissez un compte médecin déjà inscrit, puis complétez sa spécialité et ses informations professionnelles.
            </p>
        </div>
        <div class="bg-[#f2f3ff] p-2 rounded-xl flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-[#004ac6] shadow-sm">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">verified_user</span>
            </div>
            <div>
                <span class="block text-[11px] leading-4 text-[#434655] uppercase tracking-wider">Étape de validation</span>
                <span class="text-[12px] leading-4 text-[#131b2e] font-semibold">Création du profil médical</span>
            </div>
        </div>
    </div>

    @if($users->isEmpty())
        <div class="bg-white rounded-xl p-10 text-center shadow-sm">
            <span class="material-symbols-outlined text-5xl text-[#c3c6d7] block mb-3" aria-hidden="true">person_off</span>
            <p class="font-semibold text-[#131b2e]">Aucun compte médecin en attente</p>
            <p class="text-sm text-[#434655] mt-1 mb-4">Un médecin doit d’abord s’inscrire avec le rôle médecin.</p>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex px-4 py-2 bg-[#2563eb] text-white rounded-lg text-sm font-semibold">
                Créer un utilisateur
            </a>
        </div>
    @else
        <div class="bg-[#e2e7ff] rounded-xl p-4 mb-6 flex items-start gap-4 relative overflow-hidden shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-[#2563eb] text-white flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">info</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-1">
                    <h2 class="font-display text-[16px] leading-6 text-[#131b2e]">Compte déjà inscrit requis</h2>
                    <span class="px-2 rounded-full bg-white text-[#004ac6] text-[11px] leading-4 font-bold">Obligatoire</span>
                </div>
                <p class="text-[13px] leading-5 text-[#434655] mt-1">
                    La spécialité est obligatoire. Valider le médecin crée le profil médical et le rend réservable.
                </p>
            </div>
        </div>

        <form method="POST"
              action="{{ route('admin.doctors.store') }}"
              enctype="multipart/form-data"
              id="doctor-create-form"
              class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            @csrf

            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-4 bg-[#f2f3ff] -mx-6 -mt-6 px-6 pt-4 rounded-t-xl">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-[#2563eb] text-[#eeefff] text-[11px] leading-4 flex items-center justify-center font-bold">1</span>
                            <div>
                                <h3 class="font-display text-[20px] leading-7 text-[#131b2e]">Compte médecin</h3>
                                <p class="text-[13px] leading-5 text-[#434655]">Compte déjà inscrit, en attente de profil médical</p>
                            </div>
                        </div>
                        <span class="text-[11px] leading-4 text-[#737686]">Étape 1/3</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1" for="user_id">
                                Compte à valider <span class="text-[#ba1a1a] font-bold">*</span>
                            </label>
                            <div>
                                <x-ui.select name="user_id" id="user_id" icon="person" label="Compte à valider" required x-model="userId">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            @error('user_id') <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1">Nom</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[20px]" aria-hidden="true">badge</span>
                                <input class="w-full h-11 pl-10 pr-4 bg-[#f2f3ff] rounded-lg text-[15px] leading-6 text-[#434655] cursor-not-allowed"
                                       type="text"
                                       :value="selectedUser ? selectedUser.name : ''"
                                       readonly
                                       tabindex="-1">
                            </div>
                            <p class="text-[13px] leading-5 text-[#434655] mt-1">Issu du compte utilisateur. Non modifiable ici.</p>
                        </div>
                        <div>
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1">Adresse e-mail</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[20px]" aria-hidden="true">alternate_email</span>
                                <input class="w-full h-11 pl-10 pr-4 bg-[#f2f3ff] rounded-lg text-[15px] leading-6 text-[#434655] cursor-not-allowed"
                                       type="email"
                                       :value="selectedUser ? selectedUser.email : ''"
                                       readonly
                                       tabindex="-1">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1" for="phone">Téléphone</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[20px]" aria-hidden="true">call</span>
                                <input class="w-full h-11 pl-10 pr-4 bg-[#f2f3ff] rounded-lg text-[15px] leading-6 text-[#131b2e] placeholder:text-[#737686] focus:outline-none focus:bg-white shadow-sm"
                                       id="phone"
                                       name="phone"
                                       type="tel"
                                       placeholder="Optionnel"
                                       value="{{ old('phone') }}">
                            </div>
                            @error('phone') <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-4 bg-[#f2f3ff] -mx-6 -mt-6 px-6 pt-4 rounded-t-xl">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-[#2563eb] text-[#eeefff] text-[11px] leading-4 flex items-center justify-center font-bold">2</span>
                            <div>
                                <h3 class="font-display text-[20px] leading-7 text-[#131b2e]">Informations professionnelles</h3>
                                <p class="text-[13px] leading-5 text-[#434655]">Spécialité, cabinet et présentation</p>
                            </div>
                        </div>
                        <span class="text-[11px] leading-4 text-[#737686]">Étape 2/3</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1" for="speciality_id">
                                Spécialité médicale principale <span class="text-[#ba1a1a] font-bold">*</span>
                            </label>
                            <div>
                                <x-ui.select name="speciality_id" id="speciality_id" icon="category" label="Spécialité médicale principale" required x-model="specialityId">
                                    <option value="">Sélectionner une spécialité...</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            @error('speciality_id') <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1" for="address">Adresse du cabinet</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[20px]" aria-hidden="true">apartment</span>
                                <input class="w-full h-11 pl-10 pr-4 bg-[#f2f3ff] rounded-lg text-[15px] leading-6 text-[#131b2e] placeholder:text-[#737686] focus:outline-none focus:bg-white shadow-sm"
                                       id="address"
                                       name="address"
                                       type="text"
                                       placeholder="Adresse du cabinet"
                                       value="{{ old('address') }}">
                            </div>
                            @error('address') <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1" for="bio">Biographie</label>
                            <textarea class="w-full p-4 bg-[#f2f3ff] rounded-lg text-[15px] leading-6 text-[#131b2e] placeholder:text-[#737686] focus:outline-none focus:bg-white shadow-sm"
                                      id="bio"
                                      name="bio"
                                      rows="3"
                                      placeholder="Présentation affichée sur la fiche médecin">{{ old('bio') }}</textarea>
                            @error('bio') <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[14px] leading-5 text-[#131b2e] mb-1" for="photo">Photo du cabinet</label>
                            <input class="w-full rounded-lg bg-[#f2f3ff] text-[13px] leading-5 text-[#131b2e] shadow-sm file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-[#eaedff] file:text-[#004ac6] file:font-semibold"
                                   id="photo"
                                   name="photo"
                                   type="file"
                                   accept="image/jpeg,image/png,image/jpg,image/gif">
                            <p class="text-[11px] leading-4 text-[#737686] mt-1">JPEG, PNG, GIF — max 2 Mo. Distincte de la photo de compte.</p>
                            @error('photo') <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-7 h-7 rounded-full bg-[#2563eb] text-[#eeefff] text-[11px] leading-4 flex items-center justify-center font-bold">3</span>
                        <h3 class="font-display text-[20px] leading-7 text-[#131b2e]">Statut initial</h3>
                    </div>
                    <p class="text-[13px] leading-5 text-[#434655] mb-4">
                        Valider ce formulaire crée le profil médical. Le médecin devient alors visible pour la réservation.
                    </p>
                    <div class="p-4 rounded-xl bg-[#f2f3ff]">
                        <div class="flex items-center justify-between">
                            <span class="font-display text-[16px] leading-6 text-[#131b2e]">Profil à valider</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-[#007d55]"></span>
                        </div>
                        <p class="text-[13px] leading-5 text-[#434655] mt-1">
                            Aucun brouillon, statut Actif / Inactif ou suspension n’est enregistré ici.
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[11px] leading-4 uppercase tracking-wider text-[#737686] font-semibold">Aperçu</span>
                        <span class="material-symbols-outlined text-[#737686] text-[18px]" aria-hidden="true">badge</span>
                    </div>
                    <div class="flex items-center gap-4 p-4 -mx-6 bg-[#f2f3ff]">
                        <div class="w-16 h-16 rounded-full bg-[#e2e7ff] text-[#004ac6] flex items-center justify-center text-lg font-bold shadow-sm"
                             x-text="initials(selectedUser?.name)"></div>
                        <div>
                            <span class="inline-block px-2 py-0.5 rounded-full bg-[#86f2e4]/70 text-[#006f66] text-[11px] leading-4 font-semibold mb-1"
                                  x-text="selectedSpecialty ? selectedSpecialty.name : 'Spécialité à choisir'"></span>
                            <h4 class="font-display text-[16px] leading-6 text-[#131b2e]" x-text="selectedUser ? selectedUser.name : 'Compte à valider'"></h4>
                            <span class="text-[13px] leading-5 text-[#434655]" x-text="selectedUser ? selectedUser.email : ''"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-12 bg-white rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                <a class="inline-flex items-center justify-center h-11 px-4 rounded-lg text-[14px] leading-5 text-[#434655] hover:bg-[#e2e7ff] hover:text-[#131b2e] transition-colors"
                   href="{{ route('admin.doctors.index') }}">
                    Annuler
                </a>
                <button class="w-full sm:w-auto h-11 px-6 rounded-lg bg-[#004ac6] text-white text-[14px] leading-5 font-semibold hover:bg-[#003ea8] transition-all shadow-sm flex items-center justify-center gap-1"
                        type="submit">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">how_to_reg</span>
                    <span>Valider le médecin</span>
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
