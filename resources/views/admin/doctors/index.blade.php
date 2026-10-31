@extends('layouts.admin')
@section('page-title', 'Médecins')
@section('page-subtitle', 'Gérez les médecins, leurs spécialités et leur statut de validation sur la plateforme.')

@php
    $filters ??= ['search' => '', 'speciality_id' => null, 'status' => null, 'sort' => 'name', 'direction' => 'asc'];
    $stats ??= ['total' => 0, 'withSlots' => 0, 'withoutSlots' => 0, 'pending' => 0];
    $specialties ??= collect();
    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? 'M', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    };
    $hasFilters = $filters['search'] !== '' || $filters['speciality_id'] || $filters['status'];
@endphp

@section('page-actions')
    <button type="button"
            class="sc-action-btn sc-action-btn--ghost"
            @click="showToast('Export CSV non configuré — aperçu visuel uniquement.')">
        <span class="material-symbols-outlined" aria-hidden="true">file_download</span>
        Exporter (CSV)
    </button>
    <a href="{{ route('admin.doctors.create') }}"
       class="sc-action-btn sc-action-btn--primary">
        <span class="material-symbols-outlined" aria-hidden="true">add</span>
        Ajouter un médecin
    </a>
@endsection

@section('admin-content')
<div class="sc-stack w-full">
    <div class="flex flex-col gap-1 min-w-0">
        <div class="flex items-center gap-1 text-[11px] text-[#434655]">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6]">Plateforme SantéConnect</a>
            <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
            <span class="text-[#004ac6] font-semibold">Médecins &amp; Praticiens</span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 w-full">
        <article class="p-6 rounded-xl bg-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#004ac6]/5"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] uppercase tracking-wider font-semibold text-[#434655]">Total praticiens</span>
                <div class="w-10 h-10 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#004ac6]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">stethoscope</span>
                </div>
            </div>
            <p class="font-display text-[40px] leading-[48px] font-bold">{{ $stats['total'] }}</p>
            <p class="text-[13px] text-[#434655] mt-1">Profils médecins validés</p>
        </article>
        <article class="p-6 rounded-xl bg-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#007d55]/10"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] uppercase tracking-wider font-semibold text-[#434655]">Avec créneau libre</span>
                <div class="w-10 h-10 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#006242]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">event_available</span>
                </div>
            </div>
            <p class="font-display text-[40px] leading-[48px] font-bold">{{ $stats['withSlots'] }}</p>
            <p class="text-[13px] text-[#434655] mt-1">Disponibles sur l’agenda</p>
        </article>
        <article class="p-6 rounded-xl bg-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#86f2e4]/20"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] uppercase tracking-wider font-semibold text-[#434655]">En attente de validation</span>
                <div class="w-10 h-10 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#006a61]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">pending_actions</span>
                </div>
            </div>
            <p class="font-display text-[40px] leading-[48px] font-bold">{{ $stats['pending'] }}</p>
            <p class="text-[13px] text-[#434655] mt-1">Comptes médecin sans profil</p>
        </article>
        <article class="p-6 rounded-xl bg-white shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#ffdad6]/40"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] uppercase tracking-wider font-semibold text-[#434655]">Sans créneau à venir</span>
                <div class="w-10 h-10 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#ba1a1a]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">event_busy</span>
                </div>
            </div>
            <p class="font-display text-[40px] leading-[48px] font-bold">{{ $stats['withoutSlots'] }}</p>
            <p class="text-[13px] text-[#434655] mt-1">Aucun créneau libre recensé</p>
        </article>
    </div>

    <form method="GET" action="{{ route('admin.doctors.index') }}" class="p-4 rounded-xl bg-white shadow-sm">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row items-stretch gap-3">
                <div class="relative flex-1 min-w-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686] text-[20px] pointer-events-none" aria-hidden="true">search</span>
                    <label for="admin-doctor-search" class="sr-only">Rechercher un médecin</label>
                    <input id="admin-doctor-search"
                           class="w-full h-11 pl-10 pr-4 rounded-xl border-0 bg-[#f2f3ff] text-[13px] text-[#131b2e] placeholder:text-[#737686] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/25 focus:bg-white"
                           type="search" name="search" value="{{ $filters['search'] }}"
                           placeholder="Rechercher par nom, e-mail ou téléphone">
                </div>
                <div class="flex items-stretch gap-2 shrink-0">
                    <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
                    <button type="submit" class="h-11 px-5 rounded-xl bg-[#2563eb] hover:bg-[#004ac6] text-white text-[13px] font-semibold whitespace-nowrap">
                        Filtrer
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('admin.doctors.index') }}" class="h-11 w-11 rounded-xl bg-[#eaedff] text-[#434655] hover:text-[#131b2e] inline-flex items-center justify-center" title="Réinitialiser">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">refresh</span>
                            <span class="sr-only">Réinitialiser</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <x-ui.select name="speciality_id" id="admin-doctor-speciality" icon="medical_services" label="Filtrer par spécialité">
                    <option value="">Toutes les spécialités</option>
                    @foreach($specialties as $specialty)
                        <option value="{{ $specialty->id }}" @selected((int) $filters['speciality_id'] === $specialty->id)>{{ $specialty->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="status" id="admin-doctor-status" icon="filter_list" label="Filtrer par statut">
                    <option value="">Tous les statuts</option>
                    <option value="validated" @selected($filters['status'] === 'validated')>Validé</option>
                    <option value="pending" @selected($filters['status'] === 'pending')>En attente</option>
                </x-ui.select>
                <x-ui.select name="sort" id="admin-doctor-sort" icon="sort" label="Trier la liste">
                    <option value="name" @selected($filters['sort'] === 'name')>Trier par nom</option>
                    <option value="speciality" @selected($filters['sort'] === 'speciality')>Trier par spécialité</option>
                    <option value="created" @selected($filters['sort'] === 'created')>Trier par date</option>
                </x-ui.select>
            </div>
        </div>
    </form>

    <div class="rounded-xl bg-white shadow-sm overflow-hidden flex flex-col">
        <div class="w-full">
            <table class="w-full text-left border-collapse table-fixed">
                <thead>
                    <tr class="bg-[#f2f3ff] text-[#434655] text-[11px] uppercase tracking-wider">
                        <th class="py-4 pl-4 lg:pl-6 pr-3 w-[26%]" scope="col">Médecin</th>
                        <th class="py-4 px-3 w-[24%] hidden md:table-cell" scope="col">Coordonnées</th>
                        <th class="py-4 px-3 w-[16%]" scope="col">Spécialité</th>
                        <th class="py-4 px-3 w-[14%] hidden sm:table-cell" scope="col">Statut</th>
                        <th class="py-4 px-3 w-[10%] hidden lg:table-cell" scope="col">Inscription</th>
                        <th class="py-4 pr-4 lg:pr-6 pl-3 w-[10%] text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eaedff] text-[13px]">
                    @foreach($pendingDoctors as $pending)
                        <tr class="hover:bg-[#f2f3ff]/50">
                            <td class="py-4 pl-4 lg:pl-6 pr-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="relative w-11 h-11 shrink-0">
                                        <div class="w-11 h-11 rounded-full bg-[#004ac6] text-white flex items-center justify-center font-display text-[14px] font-bold">
                                            {{ $initials($pending->name) }}
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-[#004ac6] ring-2 ring-white"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-display text-[16px] leading-6 font-semibold truncate">{{ $pending->name }}</p>
                                        <p class="text-[11px] text-[#004ac6] font-medium mt-0.5 truncate">Profil non créé</p>
                                        <p class="md:hidden text-[11px] text-[#434655] truncate mt-0.5">{{ $pending->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-3 hidden md:table-cell">
                                <p class="font-medium truncate">{{ $pending->email }}</p>
                                <p class="text-[11px] text-[#434655]">Compte médecin inscrit</p>
                            </td>
                            <td class="py-4 px-3">
                                <span class="inline-flex max-w-full items-center px-3 py-1 rounded-full bg-[#e2e7ff] text-[#434655] text-[12px] font-semibold truncate">Non assignée</span>
                            </td>
                            <td class="py-4 px-3 hidden sm:table-cell">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#dae2fd] text-[#434655] text-[11px] font-semibold">
                                    <span class="material-symbols-outlined text-[14px] text-[#004ac6]" aria-hidden="true">hourglass_top</span>
                                    En attente
                                </span>
                            </td>
                            <td class="py-4 px-3 text-[#434655] text-[12px] font-semibold hidden lg:table-cell">
                                {{ $pending->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="py-4 pr-4 lg:pr-6 pl-3 text-right">
                                <a href="{{ route('admin.doctors.create', ['user_id' => $pending->id]) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#2563eb] text-white text-[11px] font-semibold hover:bg-[#004ac6]">
                                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">check</span>
                                    Valider
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    @forelse($doctors as $doctor)
                        <tr class="hover:bg-[#f2f3ff]/50 group">
                            <td class="py-4 pl-4 lg:pl-6 pr-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="relative w-11 h-11 shrink-0">
                                        @if($doctor->photo)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($doctor->photo) }}" alt="Photo de Dr {{ $doctor->user->name }}" class="w-11 h-11 rounded-full object-cover shadow-sm">
                                        @elseif($doctor->user->profile_image_url)
                                            <img src="{{ $doctor->user->profile_image_url }}" alt="Photo de Dr {{ $doctor->user->name }}" class="w-11 h-11 rounded-full object-cover shadow-sm">
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-[#004ac6] text-white flex items-center justify-center font-display text-[14px] font-bold">
                                                {{ $initials($doctor->user->name) }}
                                            </div>
                                        @endif
                                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-[#006a61] ring-2 ring-white"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-display text-[16px] leading-6 font-semibold truncate group-hover:text-[#004ac6]">Dr {{ $doctor->user->name }}</p>
                                        <p class="text-[11px] text-[#006a61] font-medium mt-0.5">Profil validé</p>
                                        <p class="md:hidden text-[11px] text-[#434655] truncate mt-0.5">{{ $doctor->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-3 hidden md:table-cell">
                                <p class="font-medium truncate">{{ $doctor->user->email }}</p>
                                <p class="text-[11px] text-[#434655] truncate">{{ $doctor->phone ?: 'Téléphone non renseigné' }}</p>
                            </td>
                            <td class="py-4 px-3">
                                <span class="inline-flex max-w-full items-center gap-1.5 px-3 py-1 rounded-full bg-[#eaedff] text-[#004ac6] text-[12px] font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#004ac6] shrink-0"></span>
                                    <span class="truncate">{{ $doctor->speciality->name }}</span>
                                </span>
                            </td>
                            <td class="py-4 px-3 hidden sm:table-cell">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#86f2e4]/40 text-[#005049] text-[11px] font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-[#006a61]"></span>
                                    Validé
                                </span>
                            </td>
                            <td class="py-4 px-3 text-[#434655] text-[12px] font-semibold hidden lg:table-cell">
                                {{ $doctor->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="py-4 pr-4 lg:pr-6 pl-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.doctors.edit', $doctor) }}"
                                       class="p-2 rounded-lg text-[#434655] hover:text-[#004ac6] hover:bg-[#eaedff]"
                                       title="Modifier la fiche">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">edit</span>
                                        <span class="sr-only">Éditer</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" class="inline"
                                          onsubmit="return confirm('Supprimer le profil de Dr {{ addslashes($doctor->user->name) }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-[#434655] hover:text-[#ba1a1a] hover:bg-[#ffdad6]" title="Supprimer">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">delete</span>
                                            <span class="sr-only">Supprimer</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @if($pendingDoctors->isEmpty())
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-[#434655]">
                                    <span class="material-symbols-outlined text-4xl text-[#c3c6d7] block mb-2" aria-hidden="true">stethoscope</span>
                                    <p class="font-semibold text-[#131b2e]">Aucun médecin validé</p>
                                    <p class="text-sm mt-1 mb-4">Validez un compte médecin inscrit pour le rendre réservable.</p>
                                    <a href="{{ route('admin.doctors.create') }}" class="inline-flex items-center px-4 py-2 bg-[#004ac6] text-white rounded-lg text-sm font-semibold">Valider un médecin</a>
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($doctors->total() > 0)
            <div class="p-4 bg-[#f2f3ff] sc-pager">
                <p class="sc-pager__label">
                    Affichage de <span class="font-semibold text-[#131b2e]">{{ $doctors->firstItem() }}</span>
                    à <span class="font-semibold text-[#131b2e]">{{ $doctors->lastItem() }}</span>
                    sur <span class="font-semibold text-[#131b2e]">{{ $doctors->total() }}</span> praticiens validés
                </p>
                <div class="sc-pager__pages">{{ $doctors->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
