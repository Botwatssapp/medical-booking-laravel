@extends('layouts.admin')
@section('page-title', 'Modifier le profil médecin')
@section('page-subtitle', 'Dr '.$doctor->user->name)

@php
    $appointmentCount ??= 0;
    $monthAppointmentCount ??= 0;
    $availableSlots ??= 0;
    $specialties ??= collect();
    $user = $doctor->user;
    $photoUrl = $doctor->photo
        ? \Illuminate\Support\Facades\Storage::url($doctor->photo)
        : $user?->profile_image_url;

    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? 'M', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    };

    $specialtyIcon = function (?string $name): string {
        $label = mb_strtolower((string) $name);

        return match (true) {
            str_contains($label, 'cardio') => 'cardiology',
            str_contains($label, 'pédiat') || str_contains($label, 'pediat') || str_contains($label, 'néonat') => 'child_care',
            str_contains($label, 'dermat') => 'dermatology',
            str_contains($label, 'neuro') => 'neurology',
            str_contains($label, 'général') || str_contains($label, 'general') || str_contains($label, 'urgence') => 'medical_services',
            str_contains($label, 'gériat') || str_contains($label, 'geriat') => 'elderly',
            str_contains($label, 'ophtal') => 'ophthalmology',
            str_contains($label, 'orl') => 'hearing',
            str_contains($label, 'ortho') => 'orthopedics',
            str_contains($label, 'gyné') || str_contains($label, 'gyne') => 'pregnancy',
            str_contains($label, 'psych') => 'psychology',
            str_contains($label, 'pneumo') => 'pulmonology',
            str_contains($label, 'urolog') => 'urology',
            default => 'stethoscope',
        };
    };
@endphp

@section('admin-content')
<div class="sc-stack w-full">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <nav aria-label="Fil d'Ariane" class="flex items-center gap-1 text-[11px] leading-4 text-[#434655]">
            <a class="hover:text-[#004ac6] transition-colors" href="{{ route('admin.dashboard') }}">Admin</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <a class="hover:text-[#004ac6] transition-colors" href="{{ route('admin.doctors.index') }}">Médecins</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <span class="text-[#131b2e] font-semibold">Modifier</span>
        </nav>
        <a class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-white text-[#131b2e] text-[14px] leading-5 font-semibold shadow-sm hover:bg-[#eaedff] transition-all"
           href="{{ route('admin.doctors.index') }}">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            <span>Retour à la liste des médecins</span>
        </a>
    </div>

    <div class="flex flex-col gap-1">
        <h1 class="font-display text-[28px] leading-9 text-[#131b2e] tracking-tight">Modifier le profil médecin</h1>
        <p class="text-[15px] leading-6 text-[#434655]">Modifiez la spécialité, les coordonnées et la présentation de ce médecin.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-[#004ac6]/5 pointer-events-none"></div>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="relative">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}"
                             alt="Photo de {{ $user?->name }}"
                             class="w-24 h-24 rounded-xl object-cover shadow-sm ring-4 ring-[#f2f3ff]">
                    @else
                        <div class="w-24 h-24 rounded-xl bg-[#e2e7ff] text-[#004ac6] flex items-center justify-center text-2xl font-bold shadow-sm ring-4 ring-[#f2f3ff]">
                            {{ $initials($user?->name) }}
                        </div>
                    @endif
                    <label for="photo"
                           class="absolute bottom-1 right-1 p-1.5 bg-[#004ac6] text-white rounded-lg shadow-sm hover:bg-[#004ac6]/90 cursor-pointer"
                           title="Mettre à jour la photo">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">photo_camera</span>
                    </label>
                </div>
                <div class="flex flex-col gap-1.5">
                    <div class="flex flex-wrap items-center gap-1">
                        <h2 class="font-display text-[20px] leading-7 text-[#131b2e]">{{ $user?->name }}</h2>
                        <span class="text-[11px] leading-4 px-2 py-0.5 rounded-full bg-[#86f2e4]/60 text-[#006f66] font-medium">
                            Compte #{{ $user?->id }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1">
                        <span class="inline-flex items-center gap-1 text-[11px] leading-4 px-2.5 py-0.5 rounded-full bg-[#e2e7ff] text-[#004ac6] font-semibold">
                            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">{{ $specialtyIcon($doctor->speciality?->name) }}</span>
                            {{ $doctor->speciality?->name ?: 'Spécialité non renseignée' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] leading-4 px-2.5 py-0.5 rounded-full bg-[#6ffbbe] text-[#002113] font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#006242]"></span>
                            Profil validé
                        </span>
                    </div>
                    <p class="text-[13px] leading-5 text-[#434655]">{{ $user?->email }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-[#f2f3ff] p-2 rounded-xl">
                <div class="flex items-center gap-3 px-3 py-2 bg-white rounded-lg">
                    <span class="material-symbols-outlined text-[#004ac6] text-[24px] p-1.5 rounded-lg bg-[#dbe1ff]" aria-hidden="true">calendar_clock</span>
                    <div class="flex flex-col">
                        <span class="font-display text-[16px] leading-6 text-[#131b2e] leading-tight">{{ $monthAppointmentCount }}</span>
                        <span class="text-[11px] leading-4 text-[#434655]">RDV ce mois</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 px-3 py-2 bg-white rounded-lg">
                    <span class="material-symbols-outlined text-[#006a61] text-[24px] p-1.5 rounded-lg bg-[#89f5e7]" aria-hidden="true">event_available</span>
                    <div class="flex flex-col">
                        <span class="font-display text-[16px] leading-6 text-[#131b2e] leading-tight">{{ $availableSlots }}</span>
                        <span class="text-[11px] leading-4 text-[#434655]">Créneaux disponibles</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 px-3 py-2 bg-white rounded-lg">
                    <span class="material-symbols-outlined text-[#737686] text-[24px] p-1.5 rounded-lg bg-[#e2e7ff]" aria-hidden="true">calendar_month</span>
                    <div class="flex flex-col">
                        <span class="font-display text-[16px] leading-6 text-[#131b2e] leading-tight">{{ $appointmentCount }}</span>
                        <span class="text-[11px] leading-4 text-[#434655]">RDV enregistrés</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('admin.doctors.update', $doctor) }}"
          enctype="multipart/form-data"
          id="doctor-edit-form"
          class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2">
                <div class="flex items-center gap-1">
                    <div class="w-1.5 h-6 rounded-full bg-[#004ac6]"></div>
                    <h3 class="font-display text-[16px] leading-6 text-[#131b2e]">1. Informations personnelles</h3>
                </div>
                <span class="text-[11px] leading-4 text-[#737686] uppercase tracking-wider font-semibold">Compte lié</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-[12px] leading-4 text-[#131b2e] font-medium" for="linked-name">Nom</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">person</span>
                        <input class="w-full h-[42px] pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[13px] leading-5 text-[#434655] cursor-not-allowed"
                               id="linked-name"
                               type="text"
                               value="{{ $user?->name }}"
                               readonly
                               tabindex="-1">
                    </div>
                    <p class="text-[11px] leading-4 text-[#737686]">Le propriétaire du profil ne peut pas être réassigné.</p>
                </div>
                <div class="space-y-1">
                    <label class="text-[12px] leading-4 text-[#131b2e] font-medium" for="linked-email">Adresse e-mail</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">mail</span>
                        <input class="w-full h-[42px] pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[13px] leading-5 text-[#434655] cursor-not-allowed"
                               id="linked-email"
                               type="email"
                               value="{{ $user?->email }}"
                               readonly
                               tabindex="-1">
                    </div>
                    <p class="text-[11px] leading-4 text-[#737686]">Modifiable depuis la fiche utilisateur.</p>
                </div>
                <div class="space-y-1">
                    <label class="text-[12px] leading-4 text-[#131b2e] font-medium" for="phone">Téléphone</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">call</span>
                        <input class="w-full h-[42px] pl-10 pr-3 rounded-lg bg-white text-[13px] leading-5 text-[#131b2e] shadow-sm focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20"
                               id="phone"
                               name="phone"
                               type="tel"
                               value="{{ old('phone', $doctor->phone) }}">
                    </div>
                    @error('phone') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-[12px] leading-4 text-[#131b2e] font-medium" for="address">Adresse du cabinet</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[18px]" aria-hidden="true">location_on</span>
                        <input class="w-full h-[42px] pl-10 pr-3 rounded-lg bg-white text-[13px] leading-5 text-[#131b2e] shadow-sm focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20"
                               id="address"
                               name="address"
                               type="text"
                               value="{{ old('address', $doctor->address) }}">
                    </div>
                    <p class="text-[11px] leading-4 text-[#737686]">Adresse visible par les patients lors de la réservation.</p>
                    @error('address') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-[12px] leading-4 text-[#131b2e] font-medium" for="photo">Photo du cabinet</label>
                    <input class="w-full rounded-lg bg-white text-[13px] leading-5 text-[#131b2e] shadow-sm file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-[#eaedff] file:text-[#004ac6] file:font-semibold"
                           id="photo"
                           name="photo"
                           type="file"
                           accept="image/jpeg,image/png,image/jpg,image/gif">
                    <p class="text-[11px] leading-4 text-[#737686]">JPEG, PNG, GIF — max 2 Mo. Distincte de la photo de compte.</p>
                    @error('photo') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2">
                <div class="flex items-center gap-1">
                    <div class="w-1.5 h-6 rounded-full bg-[#006a61]"></div>
                    <h3 class="font-display text-[16px] leading-6 text-[#131b2e]">2. Informations professionnelles</h3>
                </div>
                <span class="text-[11px] leading-4 text-[#737686] uppercase tracking-wider font-semibold">Exercice</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1 md:col-span-2">
                    <label class="text-[12px] leading-4 text-[#131b2e] font-medium" for="speciality_id">Spécialité principale <span class="text-[#ba1a1a]">*</span></label>
                    <div>
                        <x-ui.select name="speciality_id" id="speciality_id" icon="stethoscope" label="Spécialité principale">
                            @foreach($specialties as $specialty)
                                <option value="{{ $specialty->id }}" {{ (int) old('speciality_id', $doctor->speciality_id) === $specialty->id ? 'selected' : '' }}>
                                    {{ $specialty->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('speciality_id') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-[12px] leading-4 text-[#131b2e] font-medium" for="bio">Biographie</label>
                    <textarea class="w-full p-3 rounded-lg bg-white text-[13px] leading-5 text-[#131b2e] shadow-sm focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20"
                              id="bio"
                              name="bio"
                              rows="4">{{ old('bio', $doctor->bio) }}</textarea>
                    <p class="text-[11px] leading-4 text-[#737686]">Affichée sur la fiche médecin du portail patient.</p>
                    @error('bio') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2">
                <div class="flex items-center gap-1">
                    <div class="w-1.5 h-6 rounded-full bg-[#006242]"></div>
                    <h3 class="font-display text-[16px] leading-6 text-[#131b2e]">3. Statut du profil</h3>
                </div>
                <span class="text-[11px] leading-4 text-[#737686] uppercase tracking-wider font-semibold">Validation</span>
            </div>
            <div class="p-4 rounded-xl bg-[#f2f3ff] flex items-start gap-3">
                <span class="material-symbols-outlined text-[#006242] text-[22px] mt-0.5" aria-hidden="true">verified</span>
                <div class="flex flex-col">
                    <span class="text-[12px] leading-4 text-[#131b2e] font-semibold">Profil validé</span>
                    <span class="text-[13px] leading-5 text-[#434655]">Ce médecin a un profil rattaché à une spécialité et peut recevoir des rendez-vous. Le statut Actif / Suspendu n’existe pas dans cette application.</span>
                </div>
            </div>
        </div>
    </form>

    <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md rounded-xl shadow-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="POST"
              action="{{ route('admin.doctors.destroy', $doctor) }}"
              onsubmit="return confirm('Supprimer le profil médecin de {{ addslashes($user?->name) }} ?')">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center gap-1 text-[14px] leading-5 font-semibold text-[#ba1a1a] hover:bg-[#ffdad6]/40 px-3 py-2 rounded-lg transition-colors"
                    type="submit">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">delete</span>
                <span>Supprimer le compte praticien</span>
            </button>
        </form>
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <a class="px-5 py-2.5 rounded-lg bg-[#e2e7ff] text-[14px] leading-5 font-semibold text-[#131b2e] hover:bg-[#dae2fd] transition-colors"
               href="{{ route('admin.doctors.index') }}">
                Annuler
            </a>
            <button class="inline-flex items-center justify-center gap-1 px-6 py-2.5 rounded-lg bg-[#2563eb] text-[#eeefff] text-[14px] leading-5 font-semibold shadow-md hover:bg-[#004ac6] transition-all"
                    form="doctor-edit-form"
                    type="submit">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">check</span>
                <span>Enregistrer les modifications</span>
            </button>
        </div>
    </div>
</div>
@endsection
