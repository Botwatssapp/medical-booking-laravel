@extends('layouts.admin')
@section('page-title', 'Utilisateurs')
@section('page-subtitle', 'Gestion de tous les comptes utilisateurs')

@section('admin-content')
<div class="space-y-6">

    {{-- ── Bandeau médecins en attente ── --}}
    @if($pendingDoctors->isNotEmpty())
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-xl">pending</span>
                </div>
                <div>
                    <p class="font-bold text-amber-900">{{ $pendingDoctors->count() }} compte{{ $pendingDoctors->count() > 1 ? 's' : '' }} médecin en attente de validation</p>
                    <p class="text-sm text-amber-700">Ces médecins se sont inscrits mais n'ont pas encore de profil médical configuré.</p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($pendingDoctors as $pending)
                    <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3 border border-amber-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-sm">
                                {{ strtoupper(substr($pending->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $pending->name }}</p>
                                <p class="text-xs text-gray-500">{{ $pending->email }} · inscrit le {{ $pending->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.doctors.create', ['user_id' => $pending->id]) }}"
                           class="flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Confirmer le compte
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Filters + Add button ── --}}
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ route('admin.users.index') }}"
              class="flex items-center gap-3 flex-1 min-w-0">
            <select name="role"
                    class="border border-[#c2c6d4] rounded-xl px-4 py-2.5 text-sm text-[#0d1c2f] focus:outline-none focus:ring-2 focus:ring-[#003f87]/30">
                <option value="">Tous les rôles</option>
                <option value="patient" {{ request('role') === 'patient' ? 'selected' : '' }}>Patients</option>
                <option value="doctor"  {{ request('role') === 'doctor'  ? 'selected' : '' }}>Médecins</option>
                <option value="admin"   {{ request('role') === 'admin'   ? 'selected' : '' }}>Admins</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 border border-[#c2c6d4] hover:border-[#003f87] rounded-xl text-sm text-[#526069] hover:text-[#003f87] transition-colors">
                Filtrer
            </button>
        </form>
        <a href="{{ route('admin.users.create') }}"
           class="flex items-center gap-2 bg-[#003f87] hover:opacity-90 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-opacity shrink-0">
            <span class="material-symbols-outlined text-[18px]">person_add</span>
            Ajouter un utilisateur
        </a>
    </div>

    {{-- ── Table ── --}}
    @php
        $sortCol = request('sort', 'created_at');
        $sortDir = request('direction', 'desc');
        $sortLink = fn($col) => request()->fullUrlWithQuery(['sort' => $col, 'direction' => ($sortCol === $col && $sortDir === 'asc') ? 'desc' : 'asc', 'page' => 1]);
        $sortIcon = fn($col) => $sortCol === $col ? ($sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward') : 'unfold_more';
    @endphp
    <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8faff] border-b border-[#e0e7ff]">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">
                        <a href="{{ $sortLink('name') }}" class="flex items-center gap-1 group {{ $sortCol === 'name' ? 'text-[#003f87]' : 'hover:text-[#003f87]' }}">
                            Utilisateur
                            <span class="material-symbols-outlined text-[13px] {{ $sortCol === 'name' ? '' : 'opacity-30 group-hover:opacity-70' }}">{{ $sortIcon('name') }}</span>
                        </a>
                    </th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Rôle</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">
                        <a href="{{ $sortLink('created_at') }}" class="flex items-center gap-1 group {{ $sortCol === 'created_at' ? 'text-[#003f87]' : 'hover:text-[#003f87]' }}">
                            Inscrit le
                            <span class="material-symbols-outlined text-[13px] {{ $sortCol === 'created_at' ? '' : 'opacity-30 group-hover:opacity-70' }}">{{ $sortIcon('created_at') }}</span>
                        </a>
                    </th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f4ff]">
                @forelse($users as $user)
                    <tr class="hover:bg-[#f8faff] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full overflow-hidden shrink-0
                                            {{ $user->role === 'admin' ? 'bg-red-100' : ($user->role === 'doctor' ? 'bg-blue-100' : 'bg-green-100') }}
                                            flex items-center justify-center font-bold text-sm
                                            {{ $user->role === 'admin' ? 'text-red-700' : ($user->role === 'doctor' ? 'text-blue-700' : 'text-green-700') }}">
                                    @if($user->profile_image_url)
                                        <img src="{{ $user->profile_image_url }}" class="w-full h-full object-cover" alt="">
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#0d1c2f]">{{ $user->name }}</p>
                                    <p class="text-xs text-[#526069]">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $user->role === 'admin'   ? 'bg-red-100 text-red-800' :
                                   ($user->role === 'doctor' ? 'bg-blue-100 text-blue-800' :
                                                               'bg-green-100 text-green-800') }}">
                                {{ match($user->role) {
                                    'admin'   => 'Administrateur',
                                    'doctor'  => 'Médecin',
                                    'patient' => 'Patient',
                                    default   => ucfirst($user->role),
                                } }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->role === 'doctor')
                                @if($user->doctor)
                                    <span class="flex items-center gap-1 text-xs font-semibold text-green-700">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                        Profil actif
                                    </span>
                                @else
                                    <span class="flex items-center gap-1 text-xs font-semibold text-amber-700">
                                        <span class="material-symbols-outlined text-[14px]">pending</span>
                                        En attente
                                    </span>
                                @endif
                            @else
                                <span class="text-xs text-[#526069]">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-[#526069]">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->role === 'doctor' && !$user->doctor)
                                    <a href="{{ route('admin.doctors.create', ['user_id' => $user->id]) }}"
                                       class="text-xs font-semibold text-green-700 hover:underline">
                                        Confirmer
                                    </a>
                                @endif
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Éditer
                                </a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                          onsubmit="return confirm('Supprimer le compte de {{ addslashes($user->name) }} ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                                            Supprimer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-[#526069]">
                            <span class="material-symbols-outlined text-4xl text-[#c2c6d4] block mb-2">group</span>
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection
