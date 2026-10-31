@extends('layouts.admin')
@section('page-title', 'Spécialités')
@section('page-subtitle', 'Gérez le catalogue des spécialités médicales et l’attribution des praticiens habilités.')

@php
    $stats = array_merge([
        'total' => 0,
        'withDoctors' => 0,
        'withoutDoctors' => 0,
        'doctors' => 0,
        'avgPerSpecialty' => 0,
        'coverage' => 0,
    ], $stats ?? []);
    $filters ??= ['search' => '', 'filter' => null, 'sort' => 'name', 'direction' => 'asc'];
    $hasFilters = $filters['search'] !== '' || filled($filters['filter']);

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

    $iconWrap = function (string $icon, int $doctorsCount): string {
        if ($doctorsCount === 0) {
            return 'bg-[#e2e7ff] text-[#434655]';
        }

        return match ($icon) {
            'cardiology' => 'bg-[#ffdad6]/70 text-[#ba1a1a]',
            'child_care' => 'bg-[#dbe1ff] text-[#004ac6]',
            'dermatology' => 'bg-[#86f2e4] text-[#006f66]',
            'medical_services' => 'bg-[#6ffbbe] text-[#002113]',
            'neurology' => 'bg-[#dae2fd] text-[#004ac6]',
            'elderly' => 'bg-[#e2e7ff] text-[#434655]',
            default => 'bg-[#eaedff] text-[#004ac6]',
        };
    };

    $queryBase = array_filter([
        'search' => $filters['search'] !== '' ? $filters['search'] : null,
        'sort' => $filters['sort'] !== 'name' ? $filters['sort'] : null,
    ]);
    $pillUrl = fn (?string $filter) => route('admin.specialties.index', array_filter([
        ...$queryBase,
        'filter' => $filter,
    ]));
    $openCreate = $errors->has('name') || $errors->has('description');
@endphp

@section('admin-content')
<div class="sc-stack w-full pb-8"
     x-init="createOpen = {{ $openCreate ? 'true' : 'false' }}">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-xl shadow-sm">
        <div class="flex flex-col">
            <div class="flex items-center gap-1">
                <span class="text-[11px] leading-4 tracking-wider font-bold uppercase text-[#004ac6]">Référentiel médical</span>
                <span class="w-1.5 h-1.5 rounded-full bg-[#c3c6d7]"></span>
                <span class="text-[11px] leading-4 font-medium text-[#434655]">Catalogue interne</span>
            </div>
            <h1 class="font-display text-[28px] leading-9 font-semibold tracking-tight mt-1">Spécialités</h1>
            <p class="text-[15px] leading-6 text-[#434655]">Gérez le catalogue des spécialités médicales et l’attribution des praticiens habilités.</p>
        </div>
        <div class="flex items-center gap-2 self-start md:self-auto">
            <button type="button"
                    class="flex items-center gap-1 px-4 py-2.5 rounded-lg bg-[#e2e7ff] text-[#131b2e] text-[14px] leading-5 font-semibold hover:bg-[#dae2fd] transition-colors shadow-sm"
                    @click="showToast('Export CSV non configuré — aperçu visuel uniquement.')">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">file_download</span>
                <span>Exporter (CSV)</span>
            </button>
            <button type="button"
                    id="openModalBtn"
                    class="flex items-center gap-1 px-6 py-2.5 rounded-lg bg-[#2563eb] text-white text-[14px] leading-5 font-semibold shadow-md hover:bg-[#004ac6] transition-all"
                    @click="createOpen = true">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">add_circle</span>
                <span>Ajouter une spécialité</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <article class="bg-white p-6 rounded-xl shadow-sm flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#004ac6]/5 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between">
                <span class="text-[12px] leading-4 uppercase tracking-wider font-semibold text-[#434655]">Total spécialités</span>
                <div class="w-10 h-10 rounded-lg bg-[#eaedff] flex items-center justify-center text-[#004ac6]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">category</span>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-1">
                <span class="font-display text-[40px] leading-[48px] font-bold tracking-tight">{{ $stats['total'] }}</span>
            </div>
            <p class="text-[13px] leading-5 text-[#434655] mt-1">Spécialités enregistrées dans le catalogue</p>
        </article>

        <article class="bg-white p-6 rounded-xl shadow-sm flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#006242]/5 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between">
                <span class="text-[12px] leading-4 uppercase tracking-wider font-semibold text-[#434655]">Avec médecins</span>
                <div class="w-10 h-10 rounded-lg bg-[#f2f3ff] flex items-center justify-center text-[#006242]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">check_circle</span>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-1">
                <span class="font-display text-[40px] leading-[48px] font-bold tracking-tight">{{ $stats['withDoctors'] }}</span>
                <span class="text-[11px] leading-4 font-medium text-[#434655]">sur {{ $stats['total'] }} recensées</span>
            </div>
            <div class="w-full bg-[#e2e7ff] h-1.5 rounded-full mt-2 overflow-hidden">
                <div class="bg-[#006242] h-full rounded-full" style="width: {{ $stats['coverage'] }}%"></div>
            </div>
            <p class="text-[13px] leading-5 text-[#434655] mt-1">Spécialités rattachées à au moins un médecin</p>
        </article>

        <article class="bg-white p-6 rounded-xl shadow-sm flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#006a61]/5 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between">
                <span class="text-[12px] leading-4 uppercase tracking-wider font-semibold text-[#434655]">Médecins associés</span>
                <div class="w-10 h-10 rounded-lg bg-[#86f2e4]/40 flex items-center justify-center text-[#006f66]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">stethoscope</span>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-1">
                <span class="font-display text-[40px] leading-[48px] font-bold tracking-tight">{{ $stats['doctors'] }}</span>
                @if($stats['total'] > 0)
                    <span class="text-[11px] leading-4 font-semibold text-[#004ac6]">{{ $stats['avgPerSpecialty'] }} / spé</span>
                @endif
            </div>
            <p class="text-[13px] leading-5 text-[#434655] mt-1">Profils médecins validés rattachés</p>
        </article>

        <article class="bg-white p-6 rounded-xl shadow-sm flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#ba1a1a]/5 group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between">
                <span class="text-[12px] leading-4 uppercase tracking-wider font-semibold text-[#434655]">Sans médecin</span>
                <div class="w-10 h-10 rounded-lg bg-[#ffdad6]/60 flex items-center justify-center text-[#93000a]">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">person_off</span>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-1">
                <span class="font-display text-[40px] leading-[48px] font-bold tracking-tight {{ $stats['withoutDoctors'] > 0 ? 'text-[#ba1a1a]' : '' }}">{{ $stats['withoutDoctors'] }}</span>
                @if($stats['withoutDoctors'] > 0)
                    <span class="text-[11px] leading-4 font-semibold text-[#ba1a1a] flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">warning</span> Action requise
                    </span>
                @endif
            </div>
            <p class="text-[13px] leading-5 text-[#434655] mt-1">Nécessite affectation de praticiens</p>
        </article>
    </div>

    <div class="bg-gradient-to-r from-[#eaedff] via-[#f2f3ff] to-white p-6 rounded-xl shadow-sm flex flex-col lg:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#2563eb] text-white flex items-center justify-center shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-[26px]" aria-hidden="true">insights</span>
            </div>
            <div class="flex flex-col">
                <span class="font-display text-[16px] leading-6 font-semibold">Couverture des spécialités</span>
                <span class="text-[13px] leading-5 text-[#434655]">
                    @if($stats['withoutDoctors'] > 0)
                        {{ $stats['withoutDoctors'] }} spécialité{{ $stats['withoutDoctors'] > 1 ? 's' : '' }} n’{{ $stats['withoutDoctors'] > 1 ? 'ont' : 'a' }} encore aucun médecin rattaché.
                    @elseif($stats['total'] === 0)
                        Ajoutez une spécialité pour classer les médecins du catalogue.
                    @else
                        Toutes les spécialités ont au moins un médecin rattaché.
                    @endif
                </span>
            </div>
        </div>
        <div class="flex items-center gap-6 w-full lg:w-auto justify-end">
            <div class="flex flex-col text-right">
                <span class="text-[11px] leading-4 text-[#434655]">Spécialités avec médecin</span>
                <span class="font-display text-[16px] leading-6 font-bold text-[#006242]">{{ $stats['coverage'] }}%</span>
            </div>
            <div class="w-32 h-10 flex items-end gap-1.5 pb-1" aria-hidden="true">
                <div class="flex-1 bg-[#004ac6]/20 rounded-t h-4"></div>
                <div class="flex-1 bg-[#004ac6]/30 rounded-t h-6"></div>
                <div class="flex-1 bg-[#004ac6]/40 rounded-t h-5"></div>
                <div class="flex-1 bg-[#004ac6]/50 rounded-t h-8"></div>
                <div class="flex-1 bg-[#004ac6]/70 rounded-t h-7"></div>
                <div class="flex-1 bg-[#004ac6] rounded-t h-9"></div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.specialties.index') }}" class="bg-white p-4 rounded-xl shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#434655] text-[20px]" aria-hidden="true">search</span>
            <input class="w-full h-11 pl-11 pr-4 bg-[#f2f3ff] rounded-lg text-[13px] leading-5 text-[#131b2e] placeholder:text-[#434655] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/25 transition-all"
                   id="searchInput"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] }}"
                   placeholder="Rechercher par libellé ou description...">
        </div>
        <input type="hidden" name="filter" value="{{ $filters['filter'] }}">
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center bg-[#f2f3ff] p-1 rounded-lg">
                <a href="{{ $pillUrl(null) }}"
                   class="px-3 py-1.5 rounded-lg text-[12px] leading-4 {{ $filters['filter'] === null ? 'bg-white text-[#004ac6] font-semibold shadow-sm' : 'text-[#434655] hover:text-[#131b2e] font-medium' }}">
                    Toutes ({{ $stats['total'] }})
                </a>
                <a href="{{ $pillUrl('with') }}"
                   class="px-3 py-1.5 rounded-lg text-[12px] leading-4 {{ $filters['filter'] === 'with' ? 'bg-white text-[#004ac6] font-semibold shadow-sm' : 'text-[#434655] hover:text-[#131b2e] font-medium' }}">
                    Avec médecins ({{ $stats['withDoctors'] }})
                </a>
                <a href="{{ $pillUrl('empty') }}"
                   class="px-3 py-1.5 rounded-lg text-[12px] leading-4 {{ $filters['filter'] === 'empty' ? 'bg-white text-[#004ac6] font-semibold shadow-sm' : 'text-[#434655] hover:text-[#131b2e] font-medium' }}">
                    Sans médecin ({{ $stats['withoutDoctors'] }})
                </a>
            </div>
            <div class="h-6 w-px bg-[#e2e7ff] hidden sm:block"></div>
            <div class="flex items-center gap-1">
                <span class="text-[11px] leading-4 text-[#434655]">Trier par :</span>
                <div class="relative min-w-[16rem]">
                    <x-ui.select name="sort" label="Trier par" onchange="this.form.submit()">
                        <option value="name" @selected($filters['sort'] === 'name')>Nom (A-Z)</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Nom (Z-A)</option>
                        <option value="doctors_desc" @selected($filters['sort'] === 'doctors_desc')>Médecins affectés (Décroissant)</option>
                        <option value="created" @selected($filters['sort'] === 'created')>Plus récentes</option>
                    </x-ui.select>
                </div>
            </div>
            <button type="submit" class="h-10 px-4 rounded-lg bg-[#2563eb] text-white text-[12px] font-semibold">Filtrer</button>
            @if($hasFilters)
                <a href="{{ route('admin.specialties.index') }}" class="h-10 px-3 rounded-lg text-[12px] font-semibold text-[#434655] hover:text-[#004ac6] flex items-center">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[680px]">
                <thead>
                    <tr class="bg-[#f2f3ff]/70">
                        <th class="py-3.5 px-6 text-[12px] leading-4 uppercase tracking-wider text-[#434655] font-semibold" scope="col">Spécialité</th>
                        <th class="py-3.5 px-4 text-[12px] leading-4 uppercase tracking-wider text-[#434655] font-semibold hidden md:table-cell" scope="col">Description clinique</th>
                        <th class="py-3.5 px-4 text-[12px] leading-4 uppercase tracking-wider text-[#434655] font-semibold" scope="col">Praticiens</th>
                        <th class="py-3.5 px-4 text-[12px] leading-4 uppercase tracking-wider text-[#434655] font-semibold" scope="col">Couverture</th>
                        <th class="py-3.5 px-4 text-[12px] leading-4 uppercase tracking-wider text-[#434655] font-semibold hidden lg:table-cell" scope="col">Créée le</th>
                        <th class="py-3.5 px-6 text-[12px] leading-4 uppercase tracking-wider text-[#434655] font-semibold text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($specialties as $specialty)
                        @php
                            $icon = $specialtyIcon($specialty->name);
                            $doctorsCount = (int) $specialty->doctors_count;
                        @endphp
                        <tr class="hover:bg-[#f2f3ff]/50 transition-colors group {{ $doctorsCount === 0 ? 'bg-[#f2f3ff]/20' : '' }}">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-xl {{ $iconWrap($icon, $doctorsCount) }} flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                        <span class="material-symbols-outlined text-[24px]" aria-hidden="true">{{ $icon }}</span>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-display text-[16px] leading-6 font-semibold tracking-tight truncate">{{ $specialty->name }}</span>
                                        @if($doctorsCount === 0)
                                            <span class="text-[11px] leading-4 text-[#ba1a1a] flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#ba1a1a]"></span> Aucun médecin rattaché
                                            </span>
                                        @else
                                            <span class="text-[11px] leading-4 text-[#434655] flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#006242]"></span> Catalogue médecin
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 hidden md:table-cell max-w-sm">
                                <p class="text-[13px] leading-5 text-[#434655] line-clamp-2 {{ blank($specialty->description) ? 'italic' : '' }}">
                                    {{ $specialty->description ?: 'Aucune description renseignée.' }}
                                </p>
                            </td>
                            <td class="py-4 px-4">
                                @if($doctorsCount > 0)
                                    <a href="{{ route('admin.doctors.index', ['speciality_id' => $specialty->id]) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#eaedff] hover:bg-[#dbe1ff] text-[#004ac6] text-[12px] leading-4 font-semibold transition-all">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">person</span>
                                        <span>{{ $doctorsCount }} médecin{{ $doctorsCount > 1 ? 's' : '' }}</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#ffdad6] text-[#93000a] text-[12px] leading-4 font-semibold">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">warning</span>
                                        <span>0 médecin</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($doctorsCount > 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007d55]/20 text-[#006242] text-[11px] leading-4 font-semibold">
                                        <span class="w-2 h-2 rounded-full bg-[#006242]"></span> Avec médecins
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#e2e7ff] text-[#434655] text-[11px] leading-4 font-semibold">
                                        <span class="w-2 h-2 rounded-full bg-[#737686]"></span> Sans médecin
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 hidden lg:table-cell">
                                <span class="text-[11px] leading-4 text-[#434655]">{{ $specialty->created_at?->locale('fr')->translatedFormat('d M Y') }}</span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @if($doctorsCount === 0)
                                        <a href="{{ route('admin.doctors.create') }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-[#dbe1ff] text-[#004ac6] text-[11px] leading-4 font-semibold hover:bg-[#2563eb] hover:text-white transition-colors flex items-center gap-1"
                                           title="Affecter praticiens">
                                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">person_add</span>
                                            <span>Affecter</span>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.doctors.index', ['speciality_id' => $specialty->id]) }}"
                                           class="p-2 rounded-lg text-[#434655] hover:text-[#004ac6] hover:bg-[#eaedff] transition-colors"
                                           title="Consulter praticiens">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">visibility</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.specialties.edit', $specialty) }}"
                                       class="p-2 rounded-lg text-[#434655] hover:text-[#004ac6] hover:bg-[#eaedff] transition-colors"
                                       title="Modifier">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.specialties.destroy', $specialty) }}" class="inline"
                                          onsubmit="return confirm('Supprimer la spécialité {{ addslashes($specialty->name) }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 rounded-lg text-[#434655] hover:text-[#ba1a1a] hover:bg-[#ffdad6]/40 transition-colors"
                                                title="Supprimer la spécialité">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#434655]">
                                <span class="material-symbols-outlined text-4xl text-[#c3c6d7] block mb-2" aria-hidden="true">category</span>
                                <p class="font-semibold text-[#131b2e]">Aucune spécialité</p>
                                <p class="text-sm mt-1 mb-4">Ajoutez une spécialité avant de valider un médecin.</p>
                                <button type="button"
                                        class="inline-flex items-center px-4 py-2 bg-[#2563eb] text-white rounded-xl text-sm font-semibold"
                                        @click="createOpen = true">
                                    Ajouter une spécialité
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($specialties->total() > 0)
            <div class="p-4 bg-white sc-pager">
                <p class="sc-pager__label">
                    Affichage de <span class="font-semibold text-[#131b2e]">{{ $specialties->firstItem() }}</span>
                    à <span class="font-semibold text-[#131b2e]">{{ $specialties->lastItem() }}</span>
                    sur <span class="font-semibold text-[#131b2e]">{{ $specialties->total() }}</span> spécialités
                </p>
                <div class="sc-pager__pages">{{ $specialties->links() }}</div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#89f5e7] text-[#006a61] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]" aria-hidden="true">domain_verification</span>
            </div>
            <div class="flex flex-col">
                <span class="font-display text-[16px] leading-6 font-semibold">Catalogue unique</span>
                <span class="text-[13px] leading-5 text-[#434655]">Les spécialités servent uniquement à classer les médecins de la plateforme.</span>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#dbe1ff] text-[#004ac6] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]" aria-hidden="true">groups</span>
            </div>
            <div class="flex flex-col">
                <span class="font-display text-[16px] leading-6 font-semibold">Affectation individuelle</span>
                <span class="text-[13px] leading-5 text-[#434655]">Un médecin est rattaché à une seule spécialité depuis sa fiche.</span>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#dae2fd] text-[#131b2e] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]" aria-hidden="true">emergency</span>
            </div>
            <div class="flex flex-col">
                <span class="font-display text-[16px] leading-6 font-semibold">Urgences</span>
                <span class="text-[13px] leading-5 text-[#434655]">En cas d’urgence vitale, composez le 15 ou le 112.</span>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 flex items-center justify-center bg-[#283044]/40 backdrop-blur-sm transition-opacity"
     id="specialtyModal"
     x-show="createOpen"
     x-cloak
     x-transition.opacity
     @click.self="createOpen = false"
     @keydown.escape.window="createOpen = false">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-xl overflow-hidden mx-4 flex flex-col" @click.stop>
        <div class="px-6 pt-6 pb-4 flex items-center justify-between bg-[#f2f3ff]">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-[#2563eb] text-white flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[22px]" aria-hidden="true">category</span>
                </div>
                <div class="flex flex-col">
                    <h3 class="font-display text-[20px] leading-7 font-bold leading-tight">Ajouter une spécialité</h3>
                    <span class="text-[11px] leading-4 text-[#434655]">Nom et description du catalogue</span>
                </div>
            </div>
            <button type="button"
                    class="w-9 h-9 rounded-lg flex items-center justify-center text-[#434655] hover:bg-[#dae2fd] transition-colors"
                    @click="createOpen = false"
                    aria-label="Fermer">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.specialties.store') }}" class="p-6 flex flex-col gap-4">
            @csrf
            <div class="flex flex-col gap-1.5">
                <label class="text-[12px] leading-4 text-[#131b2e] font-semibold flex items-center justify-between" for="specName">
                    <span>Nom de la spécialité <span class="text-[#ba1a1a]">*</span></span>
                    <span class="text-[11px] leading-4 text-[#434655] font-normal">Ex. Neurologie</span>
                </label>
                <input class="w-full h-11 px-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[15px] leading-6 focus:outline-none focus:ring-2 focus:ring-[#004ac6]/30 transition-all"
                       id="specName"
                       name="name"
                       type="text"
                       required
                       maxlength="255"
                       value="{{ old('name') }}"
                       placeholder="Ex. Neurologie">
                @error('name') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-[12px] leading-4 text-[#131b2e] font-semibold" for="specDesc">
                    Description clinique concise
                </label>
                <textarea class="w-full p-4 rounded-lg bg-[#f2f3ff] text-[#131b2e] text-[13px] leading-5 focus:outline-none focus:ring-2 focus:ring-[#004ac6]/30 transition-all resize-none"
                          id="specDesc"
                          name="description"
                          rows="3"
                          maxlength="1000"
                          placeholder="Présentation claire pour orienter le patient...">{{ old('description') }}</textarea>
                @error('description') <p class="text-[#ba1a1a] text-sm">{{ $message }}</p> @enderror
                <span class="text-[11px] leading-4 text-[#434655]">Cette description apparaît dans le catalogue interne. Elle n’est pas obligatoire.</span>
            </div>
            <div class="flex items-center justify-end gap-2 pt-4">
                <button type="button"
                        class="px-6 py-2.5 rounded-lg bg-[#e2e7ff] text-[#131b2e] text-[14px] leading-5 font-semibold hover:bg-[#dae2fd] transition-colors"
                        @click="createOpen = false">
                    Annuler
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#2563eb] text-white text-[14px] leading-5 font-semibold shadow-md hover:bg-[#004ac6] transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">save</span>
                    <span>Enregistrer la spécialité</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
