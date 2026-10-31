@extends('layouts.admin')
@section('page-title', 'Utilisateurs')
@section('page-subtitle', 'Consultez et gérez les patients et les professionnels inscrits sur SantéConnect.')

@php
    $stats = array_merge([
        'total' => 0,
        'patients' => 0,
        'doctors' => 0,
        'admins' => 0,
        'pending' => 0,
        'validatedDoctors' => 0,
    ], $stats ?? []);
    $filters ??= [
        'search' => '',
        'role' => $role ?? null,
        'status' => null,
        'registered' => null,
        'sort' => 'created_at',
        'direction' => 'desc',
    ];
    $pendingDoctors ??= collect();
    $hasFilters = $filters['search'] !== ''
        || filled($filters['role'])
        || filled($filters['status'])
        || filled($filters['registered']);
    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? 'U', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    };
    $roleLabel = fn (?string $value) => match ($value) {
        'admin' => 'Administrateur',
        'doctor' => 'Médecin',
        'patient' => 'Patient',
        default => ucfirst((string) $value),
    };
@endphp

@section('page-actions')
    <button type="button"
            class="sc-action-btn sc-action-btn--ghost"
            @click="showToast('Export CSV non configuré — aperçu visuel uniquement.')">
        <span class="material-symbols-outlined text-[#006a61]" aria-hidden="true">file_download</span>
        Exporter (CSV)
    </button>
    <a href="{{ route('admin.users.create') }}"
       class="sc-action-btn sc-action-btn--primary">
        <span class="material-symbols-outlined" aria-hidden="true">person_add</span>
        Ajouter un utilisateur
    </a>
@endsection

@section('admin-content')
<div class="sc-stack w-full">
    <div class="flex items-center gap-1 text-[11px] leading-4 text-[#434655]">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-[#004ac6]">Administration</a>
        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">chevron_right</span>
        <span class="text-[#004ac6] font-semibold">Gestion utilisateurs</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 w-full">
        @php
            $userKpis = [
                [
                    'label' => 'Total inscrits',
                    'count' => $stats['total'],
                    'hint' => 'Comptes enregistrés',
                    'icon' => 'group',
                    'iconClass' => 'bg-[#eaedff] text-[#004ac6]',
                    'valueClass' => '',
                    'hintClass' => '',
                ],
                [
                    'label' => 'Patients',
                    'count' => $stats['patients'],
                    'hint' => 'Comptes patients',
                    'icon' => 'person',
                    'iconClass' => 'bg-[#dae2fd] text-[#004ac6]',
                    'valueClass' => '',
                    'hintClass' => '',
                ],
                [
                    'label' => 'Médecins',
                    'count' => $stats['doctors'],
                    'hint' => $stats['validatedDoctors'].' profil'.($stats['validatedDoctors'] > 1 ? 's' : '').' validé'.($stats['validatedDoctors'] > 1 ? 's' : ''),
                    'icon' => 'stethoscope',
                    'iconClass' => 'bg-[#86f2e4] text-[#006f66]',
                    'valueClass' => '',
                    'hintClass' => 'text-[#006a61] font-medium',
                ],
                [
                    'label' => 'Administrateurs',
                    'count' => $stats['admins'],
                    'hint' => 'Comptes administrateur',
                    'icon' => 'shield_person',
                    'iconClass' => 'bg-[#dbe1ff] text-[#00174b]',
                    'valueClass' => '',
                    'hintClass' => '',
                ],
                [
                    'label' => 'En attente',
                    'count' => $stats['pending'],
                    'hint' => 'Sans profil validé',
                    'icon' => $stats['pending'] > 0 ? 'pending_actions' : 'verified_user',
                    'iconClass' => $stats['pending'] > 0 ? 'bg-[#ffdad6] text-[#93000a]' : 'bg-[#007d55] text-[#bdffdb]',
                    'valueClass' => $stats['pending'] > 0 ? 'text-[#ba1a1a]' : '',
                    'hintClass' => '',
                ],
            ];
        @endphp
        @foreach($userKpis as $kpi)
            <article class="sc-stat-card">
                <p class="sc-stat-card__label">{{ $kpi['label'] }}</p>
                <div class="sc-stat-card__row">
                    <p class="sc-stat-card__value {{ $kpi['valueClass'] }}">{{ $kpi['count'] }}</p>
                    <div class="sc-stat-card__icon {{ $kpi['iconClass'] }}">
                        <span class="material-symbols-outlined" aria-hidden="true">{{ $kpi['icon'] }}</span>
                    </div>
                </div>
                <p class="sc-stat-card__hint {{ $kpi['hintClass'] }}">{{ $kpi['hint'] }}</p>
            </article>
        @endforeach
    </div>

    @if($pendingDoctors->isNotEmpty() && ! $hasFilters)
        <div class="bg-[#fff8e6] border border-amber-200 rounded-xl p-5 mb-6 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-xl" aria-hidden="true">pending</span>
                </div>
                <div>
                    <p class="font-bold text-amber-900">{{ $pendingDoctors->count() }} compte{{ $pendingDoctors->count() > 1 ? 's' : '' }} médecin en attente de validation</p>
                    <p class="text-sm text-amber-800">Ces médecins se sont inscrits mais n’ont pas encore de profil médical configuré.</p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($pendingDoctors as $pending)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-xl px-4 py-3 border border-amber-200">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-sm">
                                {{ $initials($pending->name) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[#131b2e]">{{ $pending->name }}</p>
                                <p class="text-xs text-[#434655]">{{ $pending->email }} · inscrit le {{ $pending->created_at?->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.doctors.create', ['user_id' => $pending->id]) }}"
                           class="flex items-center gap-1.5 bg-[#006242] hover:bg-[#005236] text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">check_circle</span>
                            Confirmer le compte
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col">
        <form method="GET" action="{{ route('admin.users.index') }}" class="p-4 sm:p-6 sc-toolbar">
            <div class="relative flex-1 min-w-0 max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#434655] text-[20px]" aria-hidden="true">search</span>
                <input class="w-full h-10 pl-10 pr-4 rounded-lg bg-[#f2f3ff] text-[13px] leading-5 text-[#131b2e] placeholder:text-[#434655] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20"
                       id="user-search"
                       type="search"
                       name="search"
                       value="{{ $filters['search'] }}"
                       placeholder="Rechercher par nom, email, téléphone...">
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-full sm:w-48">
                    <x-ui.select name="role" label="Filtrer par rôle">
                        <option value="">Tous les rôles</option>
                        <option value="patient" @selected($filters['role'] === 'patient')>Patients</option>
                        <option value="doctor" @selected($filters['role'] === 'doctor')>Médecins</option>
                        <option value="admin" @selected($filters['role'] === 'admin')>Administrateurs</option>
                    </x-ui.select>
                </div>
                <div class="relative w-full sm:w-56">
                    <x-ui.select name="status" label="Filtrer par profil médecin">
                        <option value="">Tous les profils médecin</option>
                        <option value="validated" @selected($filters['status'] === 'validated')>Profil validé</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>En attente de profil</option>
                    </x-ui.select>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#434655] text-[18px] pointer-events-none" aria-hidden="true">calendar_today</span>
                    <input type="date"
                           name="registered"
                           value="{{ $filters['registered'] }}"
                           class="h-10 pl-10 pr-3 rounded-lg bg-[#f2f3ff] text-[13px] leading-5 text-[#131b2e] focus:outline-none focus:ring-2 focus:ring-[#004ac6]/20">
                </div>
                <div class="relative w-full sm:w-44">
                    <x-ui.select name="sort" label="Trier">
                        <option value="created_at" @selected($filters['sort'] === 'created_at')>Plus récents</option>
                        <option value="name" @selected($filters['sort'] === 'name')>Nom (A-Z)</option>
                    </x-ui.select>
                </div>
                <input type="hidden" name="direction" value="{{ $filters['sort'] === 'name' ? 'asc' : 'desc' }}">
                <button type="submit" class="h-10 px-4 rounded-lg bg-[#2563eb] text-white text-[12px] font-semibold">Filtrer</button>
                @if($hasFilters)
                    <a href="{{ route('admin.users.index') }}"
                       class="h-10 px-3 rounded-lg text-[#434655] hover:text-[#ba1a1a] hover:bg-[#ffdad6]/40 text-[12px] font-semibold flex items-center gap-1.5 transition-colors"
                       title="Réinitialiser les filtres">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">restart_alt</span>
                        <span>Effacer</span>
                    </a>
                @endif
            </div>
        </form>

        <div class="px-6 pb-3 flex flex-wrap items-center gap-2 text-[11px] leading-4 text-[#434655]">
            <span class="font-medium">Filtres rapides :</span>
            <a href="{{ route('admin.users.index', ['role' => 'patient']) }}"
               class="px-2.5 py-1 rounded-full {{ $filters['role'] === 'patient' && ! $filters['status'] ? 'bg-[#2563eb] text-white' : 'bg-[#eaedff] text-[#131b2e] hover:bg-[#dae2fd]' }} font-medium transition-colors">
                Patients ({{ $stats['patients'] }})
            </a>
            <a href="{{ route('admin.users.index', ['status' => 'validated']) }}"
               class="px-2.5 py-1 rounded-full {{ $filters['status'] === 'validated' ? 'bg-[#2563eb] text-white' : 'bg-[#eaedff] text-[#131b2e] hover:bg-[#dae2fd]' }} font-medium transition-colors">
                Médecins validés ({{ $stats['validatedDoctors'] }})
            </a>
            <a href="{{ route('admin.users.index', ['status' => 'pending']) }}"
               class="px-2.5 py-1 rounded-full {{ $filters['status'] === 'pending' ? 'bg-[#2563eb] text-white' : 'bg-[#eaedff] text-[#131b2e] hover:bg-[#dae2fd]' }} font-medium transition-colors">
                Médecins en attente ({{ $stats['pending'] }})
            </a>
        </div>

        <div class="w-full">
            <table class="w-full text-left border-collapse table-fixed">
                <thead>
                    <tr class="bg-[#f2f3ff] text-[#434655] text-[12px] leading-4 uppercase tracking-wider">
                        <th class="py-3.5 pl-4 lg:pl-6 pr-3 font-semibold w-[24%]" scope="col">Utilisateur</th>
                        <th class="py-3.5 px-3 font-semibold w-[22%] hidden md:table-cell" scope="col">Email</th>
                        <th class="py-3.5 px-3 font-semibold w-[12%]" scope="col">Rôle</th>
                        <th class="py-3.5 px-3 font-semibold w-[14%] hidden lg:table-cell" scope="col">Téléphone</th>
                        <th class="py-3.5 px-3 font-semibold w-[12%] hidden sm:table-cell" scope="col">Statut</th>
                        <th class="py-3.5 pr-4 lg:pr-6 pl-3 font-semibold w-[16%] text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f2f3ff] text-[13px] leading-5 text-[#131b2e]">
                    @forelse($users as $user)
                        @php
                            $isPendingDoctor = $user->role === 'doctor' && ! $user->doctor;
                            $isValidatedDoctor = $user->role === 'doctor' && (bool) $user->doctor;
                            $phone = $user->phone ?: $user->doctor?->phone;
                        @endphp
                        <tr class="hover:bg-[#f2f3ff]/40 transition-colors group {{ $isPendingDoctor ? 'bg-amber-50/40' : '' }}">
                            <td class="py-3.5 pl-4 lg:pl-6 pr-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($user->profile_image_url)
                                        <img src="{{ $user->profile_image_url }}"
                                             alt="Photo de {{ $user->name }}"
                                             class="w-9 h-9 rounded-full object-cover shadow-sm ring-1 ring-[#c3c6d7] shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center text-[12px] font-semibold ring-1 ring-[#c3c6d7]
                                                    {{ $user->role === 'admin' ? 'bg-[#dae2fd] text-[#131b2e]' : ($user->role === 'doctor' ? 'bg-[#dbe1ff] text-[#004ac6]' : 'bg-[#eaedff] text-[#006f66]') }}">
                                            {{ $initials($user->name) }}
                                        </div>
                                    @endif
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[14px] leading-5 font-semibold truncate group-hover:text-[#004ac6] transition-colors">{{ $user->name }}</span>
                                        <span class="text-[11px] leading-4 text-[#434655] truncate">Compte #{{ $user->id }}</span>
                                        <span class="md:hidden text-[11px] text-[#434655] truncate">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-[#434655] text-[12px] hidden md:table-cell">
                                <span class="block truncate" title="{{ $user->email }}">{{ $user->email }}</span>
                            </td>
                            <td class="py-3.5 px-3">
                                @if($user->role === 'patient')
                                    <span class="inline-flex max-w-full items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#86f2e4]/40 text-[#006f66] text-[11px] leading-4 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#006a61] shrink-0"></span>
                                        <span class="truncate">Patient</span>
                                    </span>
                                @elseif($user->role === 'doctor')
                                    <span class="inline-flex max-w-full items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#dbe1ff] text-[#003ea8] text-[11px] leading-4 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#004ac6] shrink-0"></span>
                                        <span class="truncate">Médecin</span>
                                    </span>
                                @else
                                    <span class="inline-flex max-w-full items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#dae2fd] text-[#131b2e] text-[11px] leading-4 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#131b2e] shrink-0"></span>
                                        <span class="truncate">Administrateur</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 hidden lg:table-cell {{ $phone ? 'text-[#131b2e] font-medium' : 'text-[#434655] italic' }}">
                                <span class="block truncate">{{ $phone ?: 'Non renseigné' }}</span>
                            </td>
                            <td class="py-3.5 px-3 hidden sm:table-cell">
                                @if($isPendingDoctor)
                                    <span class="inline-flex max-w-full items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#dae2fd] text-[#434655] text-[11px] leading-4 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#737686] shrink-0"></span>
                                        <span class="truncate">En attente</span>
                                    </span>
                                @elseif($isValidatedDoctor)
                                    <span class="inline-flex max-w-full items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#007d55]/20 text-[#006242] text-[11px] leading-4 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#006242] shrink-0"></span>
                                        <span class="truncate">Profil validé</span>
                                    </span>
                                @else
                                    <span class="inline-flex max-w-full items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#f2f3ff] text-[#434655] text-[11px] leading-4 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#737686] shrink-0"></span>
                                        <span class="truncate">Inscrit</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 pr-4 lg:pr-6 pl-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-0.5">
                                    @if($isPendingDoctor)
                                        <a href="{{ route('admin.doctors.create', ['user_id' => $user->id]) }}"
                                           class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[#004ac6] hover:bg-[#eaedff] transition-colors"
                                           title="Confirmer le compte médecin">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">send</span>
                                            <span class="sr-only">Confirmer</span>
                                        </a>
                                    @elseif($isValidatedDoctor)
                                        <a href="{{ route('admin.doctors.edit', $user->doctor) }}"
                                           class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[#434655] hover:text-[#004ac6] hover:bg-[#eaedff] transition-colors"
                                           title="Voir fiche praticien">
                                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">badge</span>
                                            <span class="sr-only">Fiche praticien</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[#434655] hover:text-[#131b2e] hover:bg-[#eaedff] transition-colors"
                                       title="Modifier">
                                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">edit</span>
                                        <span class="sr-only">Modifier</span>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                              onsubmit="return confirm('Supprimer le compte de {{ addslashes($user->name) }} ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[#434655] hover:text-[#ba1a1a] hover:bg-[#ffdad6]/40 transition-colors"
                                                    title="Supprimer">
                                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">delete</span>
                                                <span class="sr-only">Supprimer</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#434655]">
                                <span class="material-symbols-outlined text-4xl text-[#c3c6d7] block mb-2" aria-hidden="true">group</span>
                                @if($filters['role'])
                                    <p class="font-semibold text-[#131b2e]">Aucun utilisateur pour ce rôle</p>
                                    <a href="{{ route('admin.users.index') }}" class="inline-block mt-3 text-sm font-semibold text-[#004ac6] hover:underline">Voir tous les utilisateurs</a>
                                @else
                                    <p class="font-semibold text-[#131b2e]">Aucun utilisateur</p>
                                    @if($hasFilters)
                                        <a href="{{ route('admin.users.index') }}" class="inline-block mt-3 text-sm font-semibold text-[#004ac6] hover:underline">Voir tous les utilisateurs</a>
                                    @else
                                        <a href="{{ route('admin.users.create') }}" class="inline-flex mt-3 items-center px-4 py-2 bg-[#2563eb] text-white rounded-xl text-sm font-semibold">Ajouter un utilisateur</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->total() > 0)
            <div class="p-4 lg:px-6 bg-white sc-pager">
                <p class="sc-pager__label">
                    Affichage de <span class="font-semibold text-[#131b2e]">{{ $users->firstItem() }}</span>
                    à <span class="font-semibold text-[#131b2e]">{{ $users->lastItem() }}</span>
                    sur <span class="font-semibold text-[#131b2e]">{{ $users->total() }}</span> utilisateurs
                </p>
                <div class="sc-pager__pages">{{ $users->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
