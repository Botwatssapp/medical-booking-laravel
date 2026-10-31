@extends('layouts.admin')
@section('page-title', 'Modifier la spécialité')
@section('page-subtitle', $specialty->name)

@php
    $appointmentCount ??= 0;
    $monthAppointmentCount ??= 0;
    $doctors = $specialty->doctors ?? collect();
    $doctorsCount = $doctors->count();
    $previewDoctors = $doctors->take(3);

    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? 'M', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    };

    $specialtyIcon = function (string $name): string {
        $label = mb_strtolower($name);

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
            default => 'category',
        };
    };

    $icon = $specialtyIcon((string) $specialty->name);
@endphp

@section('admin-content')
<div class="flex flex-col w-full">
    <div class="pb-4">
        <nav aria-label="Fil d'Ariane" class="flex items-center gap-1 text-[12px] leading-4 text-[#434655]">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6] transition-colors">Admin</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <a href="{{ route('admin.specialties.index') }}" class="hover:text-[#004ac6] transition-colors">Spécialités</a>
            <span class="material-symbols-outlined text-[16px] text-[#737686]" aria-hidden="true">chevron_right</span>
            <span class="text-[#131b2e] font-semibold">Modifier</span>
        </nav>
        <div class="mt-2 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="max-w-2xl">
                <h1 class="font-display text-[28px] leading-9 tracking-tight">Modifier la spécialité</h1>
                <p class="text-[15px] leading-6 text-[#434655] mt-1">
                    Modifiez le nom et la description de cette spécialité médicale.
                </p>
            </div>
            <a href="{{ route('admin.specialties.index') }}"
               class="inline-flex items-center gap-1 text-[14px] leading-5 font-semibold text-[#004ac6] hover:text-[#003ea8] transition-colors group self-start md:self-auto py-2">
                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:-translate-x-1" aria-hidden="true">arrow_back</span>
                <span>Retour au catalogue des spécialités</span>
            </a>
        </div>
    </div>

    <div class="pb-16">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
            <div class="xl:col-span-8 flex flex-col gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
                    <div class="absolute -right-16 -top-16 w-64 h-64 bg-[#004ac6]/5 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="flex items-start gap-4 z-10">
                        <div class="w-16 h-16 rounded-xl bg-[#eaedff] flex items-center justify-center text-[#004ac6] shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[36px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">{{ $icon }}</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex flex-wrap items-center gap-1 mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] leading-4 bg-[#e2e7ff] text-[#004ac6] font-semibold tracking-wide">
                                    Catalogue interne
                                </span>
                                @if($doctorsCount > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] leading-4 bg-[#6ffbbe] text-[#002113] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#006242]"></span>
                                        Avec médecins
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] leading-4 bg-[#e2e7ff] text-[#434655] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#737686]"></span>
                                        Sans médecin
                                    </span>
                                @endif
                            </div>
                            <h2 class="font-display text-[20px] leading-7 text-[#131b2e]">{{ $specialty->name }}</h2>
                            <span class="text-[12px] leading-4 text-[#434655] mt-0.5">Spécialité #{{ $specialty->id }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-6 self-stretch md:self-auto justify-between md:justify-end bg-[#f2f3ff] md:bg-transparent p-4 md:p-0 rounded-lg z-10">
                        <div class="flex flex-col text-left md:text-right">
                            <span class="text-[11px] leading-4 uppercase tracking-wider text-[#737686] font-semibold">Médecins rattachés</span>
                            <div class="flex items-center md:justify-end gap-1 mt-1">
                                @if($previewDoctors->isNotEmpty())
                                    <div class="flex -space-x-2 overflow-hidden">
                                        @foreach($previewDoctors as $previewDoctor)
                                            @if($previewDoctor->user?->profile_image_url)
                                                <img src="{{ $previewDoctor->user->profile_image_url }}"
                                                     alt=""
                                                     class="inline-block h-6 w-6 rounded-full object-cover shadow-sm">
                                            @else
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#e2e7ff] text-[#004ac6] text-[10px] font-semibold shadow-sm">
                                                    {{ $initials($previewDoctor->user?->name) }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <span class="font-display text-[16px] leading-6 text-[#131b2e] font-bold ml-1">{{ $doctorsCount }}</span>
                            </div>
                        </div>
                        <div class="w-px h-8 bg-[#dae2fd] hidden md:block"></div>
                        <div class="flex flex-col text-left md:text-right">
                            <span class="text-[11px] leading-4 uppercase tracking-wider text-[#737686] font-semibold">RDV ce mois</span>
                            <span class="font-display text-[16px] leading-6 text-[#004ac6] font-bold mt-1">{{ $monthAppointmentCount }}</span>
                        </div>
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('admin.specialties.update', $specialty) }}"
                      id="edit-specialty-form"
                      class="bg-white rounded-xl p-6 shadow-sm flex flex-col gap-6">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between pb-2">
                        <div>
                            <h3 class="font-display text-[16px] leading-6 text-[#131b2e]">Informations générales</h3>
                            <p class="text-[13px] leading-5 text-[#434655]">Nom et description affichés dans le catalogue.</p>
                        </div>
                        <span class="px-2 py-1 rounded bg-[#eaedff] text-[11px] leading-4 text-[#434655]">Fiche #{{ $specialty->id }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[14px] leading-5 text-[#131b2e] font-semibold flex items-center justify-between" for="name">
                                <span>Nom officiel de la spécialité <span class="text-[#ba1a1a]">*</span></span>
                            </label>
                            <input class="w-full h-11 px-3.5 bg-[#faf8ff] rounded-lg text-[15px] leading-6 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 transition-all"
                                   id="name"
                                   name="name"
                                   required
                                   type="text"
                                   value="{{ old('name', $specialty->name) }}">
                            @error('name')
                                <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-[14px] leading-5 text-[#131b2e] font-semibold flex items-center justify-between" for="description">
                                <span>Description</span>
                                <span class="text-[11px] leading-4 text-[#434655] font-normal">Affichée dans le catalogue</span>
                            </label>
                            <textarea class="w-full p-3.5 bg-[#faf8ff] rounded-lg text-[15px] leading-6 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20 transition-all leading-relaxed"
                                      id="description"
                                      name="description"
                                      rows="4">{{ old('description', $specialty->description) }}</textarea>
                            @error('description')
                                <p class="text-[#ba1a1a] text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-[11px] leading-4 text-[#434655]">Champ optionnel. Laissez vide si aucune description n’est nécessaire.</p>
                        </div>
                    </div>
                </form>

                <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-display text-[16px] leading-6 text-[#131b2e]">Praticiens rattachés</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] leading-4 bg-[#e2e7ff] text-[#004ac6] font-bold">{{ $doctorsCount }} au total</span>
                            </div>
                            <p class="text-[13px] leading-5 text-[#434655]">Médecins dont le profil est lié à cette spécialité.</p>
                        </div>
                        <a href="{{ route('admin.doctors.create') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#faf8ff] hover:bg-[#eaedff] text-[#131b2e] text-[14px] leading-5 font-semibold shadow-sm transition-colors self-start sm:self-auto">
                            <span class="material-symbols-outlined text-[18px] text-[#004ac6]" aria-hidden="true">person_add</span>
                            <span>+ Affecter d'autres praticiens</span>
                        </a>
                    </div>

                    @if($doctors->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-[#f2f3ff] text-[#434655] text-[11px] leading-4 uppercase tracking-wider">
                                        <th class="py-2.5 px-4 rounded-l-lg">Praticien</th>
                                        <th class="py-2.5 px-2">Contact</th>
                                        <th class="py-2.5 px-2 text-right rounded-r-lg">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f2f3ff] text-[13px] leading-5 text-[#131b2e]">
                                    @foreach($doctors as $doctor)
                                        @php
                                            $doctorName = $doctor->user?->name ?: 'Médecin #'.$doctor->id;
                                            $doctorContact = $doctor->phone ?: ($doctor->user?->phone ?: ($doctor->user?->email ?: 'Non renseigné'));
                                        @endphp
                                        <tr class="hover:bg-[#faf8ff] transition-colors">
                                            <td class="py-3 px-4">
                                                <div class="flex items-center gap-2">
                                                    @if($doctor->user?->profile_image_url)
                                                        <img src="{{ $doctor->user->profile_image_url }}"
                                                             alt="Photo de {{ $doctorName }}"
                                                             class="w-9 h-9 rounded-full object-cover">
                                                    @else
                                                        <span class="w-9 h-9 rounded-full bg-[#e2e7ff] text-[#004ac6] inline-flex items-center justify-center text-[11px] font-semibold">
                                                            {{ $initials($doctorName) }}
                                                        </span>
                                                    @endif
                                                    <div class="flex flex-col">
                                                        <span class="text-[14px] leading-5 text-[#131b2e] font-semibold leading-tight">{{ $doctorName }}</span>
                                                        <span class="text-[11px] leading-4 text-[#434655]">Profil validé</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-2 text-[#434655]">{{ $doctorContact }}</td>
                                            <td class="py-3 px-2 text-right">
                                                <a class="p-1.5 rounded-lg text-[#434655] hover:text-[#004ac6] hover:bg-[#eaedff] inline-flex transition-colors"
                                                   href="{{ route('admin.doctors.edit', $doctor) }}"
                                                   title="Fiche praticien">
                                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">open_in_new</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between pt-1 text-[12px] leading-4 text-[#434655]">
                            <span>Affichage de {{ $doctorsCount }} praticien{{ $doctorsCount > 1 ? 's' : '' }} rattaché{{ $doctorsCount > 1 ? 's' : '' }}</span>
                            <a class="text-[#004ac6] hover:text-[#003ea8] font-semibold flex items-center gap-1 transition-colors"
                               href="{{ route('admin.doctors.index', ['speciality_id' => $specialty->id]) }}">
                                Voir tous les médecins affiliés
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">chevron_right</span>
                            </a>
                        </div>
                    @else
                        <div class="py-8 text-center text-[#434655]">
                            <span class="material-symbols-outlined text-4xl text-[#c3c6d7] block mb-2" aria-hidden="true">stethoscope</span>
                            <p class="font-semibold text-[#131b2e]">Aucun médecin rattaché</p>
                            <p class="text-sm mt-1">Validez ou créez un médecin pour l’associer à cette spécialité.</p>
                        </div>
                    @endif
                </div>

                <div class="bg-[#ffdad6]/40 rounded-xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-[#ffdad6] text-[#93000a] flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[22px]" aria-hidden="true">warning</span>
                        </div>
                        <div class="flex flex-col">
                            <h4 class="font-display text-[16px] leading-6 text-[#93000a] font-semibold">Zone de danger : Supprimer cette spécialité ?</h4>
                            <p class="text-[13px] leading-5 text-[#434655] mt-1 max-w-xl">
                                La suppression archive cette spécialité.
                                @if($doctorsCount > 0)
                                    Actuellement <strong class="text-[#131b2e] font-semibold">{{ $doctorsCount }} médecin{{ $doctorsCount > 1 ? 's' : '' }} associé{{ $doctorsCount > 1 ? 's' : '' }}</strong>
                                    et {{ $appointmentCount }} rendez-vous enregistrés.
                                @else
                                    Aucun médecin n’y est rattaché.
                                @endif
                            </p>
                        </div>
                    </div>
                    <form method="POST"
                          action="{{ route('admin.specialties.destroy', $specialty) }}"
                          onsubmit="return confirm('Supprimer la spécialité {{ addslashes($specialty->name) }} ?')">
                        @csrf
                        @method('DELETE')
                        <button class="px-4 py-2.5 rounded-lg bg-white text-[#ba1a1a] hover:bg-[#ffdad6] text-[14px] leading-5 font-semibold shrink-0 flex items-center gap-1.5 transition-colors"
                                type="submit">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">delete</span>
                            <span>Supprimer la spécialité</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="xl:col-span-4 flex flex-col gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-display text-[16px] leading-6 text-[#131b2e]">Activité</h3>
                        <span class="px-2 py-0.5 rounded-full text-[11px] leading-4 bg-[#e2e7ff] text-[#434655] font-medium">Données réelles</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#faf8ff]">
                            <div class="flex items-center gap-1 text-[#434655] text-[13px] leading-5">
                                <span class="material-symbols-outlined text-[18px] text-[#004ac6]" aria-hidden="true">group</span>
                                <span>Médecins rattachés</span>
                            </div>
                            <span class="text-[14px] leading-5 text-[#131b2e] font-bold">{{ $doctorsCount }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#faf8ff]">
                            <div class="flex items-center gap-1 text-[#434655] text-[13px] leading-5">
                                <span class="material-symbols-outlined text-[18px] text-[#006a61]" aria-hidden="true">calendar_month</span>
                                <span>RDV ce mois</span>
                            </div>
                            <span class="text-[14px] leading-5 text-[#131b2e] font-bold">{{ $monthAppointmentCount }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[#faf8ff]">
                            <div class="flex items-center gap-1 text-[#434655] text-[13px] leading-5">
                                <span class="material-symbols-outlined text-[18px] text-[#006242]" aria-hidden="true">event_available</span>
                                <span>RDV enregistrés</span>
                            </div>
                            <span class="text-[14px] leading-5 text-[#131b2e] font-bold">{{ $appointmentCount }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm flex flex-col gap-4">
                    <h3 class="font-display text-[16px] leading-6 text-[#131b2e]">Historique</h3>
                    <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#e2e7ff]">
                        <div class="relative">
                            <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-[#004ac6] ring-4 ring-white"></span>
                            <span class="text-[11px] leading-4 text-[#434655]">{{ $specialty->updated_at?->locale('fr')->translatedFormat('d M Y à H:i') }}</span>
                            <p class="text-[13px] leading-5 text-[#131b2e] font-medium">Dernière mise à jour</p>
                        </div>
                        <div class="relative">
                            <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-[#737686] ring-4 ring-white"></span>
                            <span class="text-[11px] leading-4 text-[#434655]">{{ $specialty->created_at?->locale('fr')->translatedFormat('d M Y à H:i') }}</span>
                            <p class="text-[13px] leading-5 text-[#131b2e] font-medium">Création de la spécialité</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sticky bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md shadow-[0_-4px_16px_rgba(0,0,0,0.04)] px-0 py-4 z-30 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2 text-[#434655]">
                <span class="material-symbols-outlined text-[18px] text-[#006242]" aria-hidden="true">check_circle</span>
                <span class="text-[13px] leading-5 hidden sm:inline">Seuls le nom et la description sont enregistrés.</span>
            </div>
            <div class="flex items-center gap-2">
                <a class="px-4 py-2.5 rounded-lg bg-[#faf8ff] hover:bg-[#eaedff] text-[#131b2e] text-[14px] leading-5 font-semibold transition-colors"
                   href="{{ route('admin.specialties.index') }}">
                    Annuler
                </a>
                <button class="px-6 py-2.5 rounded-lg bg-[#2563eb] hover:bg-[#004ac6] text-[#eeefff] text-[14px] leading-5 font-semibold shadow-md hover:shadow-lg transition-all flex items-center gap-2"
                        form="edit-specialty-form"
                        type="submit">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">save</span>
                    <span>Enregistrer les modifications</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
