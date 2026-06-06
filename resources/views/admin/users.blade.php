@extends('layouts.app')
@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-10">
        <a href="/admin/dashboard" class="flex items-center gap-2 text-[#003f87] hover:underline">
            <span class="material-symbols-outlined">arrow_back</span>
            Retour
        </a>
        <h1 class="text-3xl font-bold text-[#0d1c2f]">Gestion des Utilisateurs</h1>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8f9ff] border-b border-[#c2c6d4]">
                <tr>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Nom</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Email</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Rôle</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Créé le</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#c2c6d4]/30">
                @forelse($users as $user)
                    <tr class="hover:bg-[#f8f9ff] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#0056b3] text-white flex items-center justify-center text-sm font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-[#0d1c2f]">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                @if($user->role === 'admin')   bg-[#eff4ff] text-[#003f87]
                                @elseif($user->role === 'doctor') bg-green-100 text-green-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#424752]">
                            {{ date('d/m/Y', strtotime($user->created_at)) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($user->role !== 'admin')
                                <form action="/admin/users/{{ $user->id }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        onclick="return confirm('Supprimer cet utilisateur ?')"
                                        class="px-3 py-1.5 border border-[#ba1a1a] text-[#ba1a1a] rounded-lg text-xs font-medium hover:bg-[#ffdad6] transition-colors"
                                    >
                                        Supprimer
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-[#526069]">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#424752]">Aucun utilisateur</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
