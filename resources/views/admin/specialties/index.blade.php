@extends('layouts.admin')
@section('page-title', 'Spécialités')
@section('page-subtitle', 'Gestion des spécialités médicales')

@section('admin-content')
<div class="space-y-6">

    <div class="flex justify-end">
        <a href="{{ route('admin.specialties.create') }}"
           class="flex items-center gap-2 bg-[#003f87] hover:opacity-90 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-opacity">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Ajouter une spécialité
        </a>
    </div>

    @php
        $sortDir  = request('direction', 'asc');
        $sortLink = request()->fullUrlWithQuery(['direction' => $sortDir === 'asc' ? 'desc' : 'asc', 'page' => 1]);
        $sortIcon = $sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward';
    @endphp
    <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8faff] border-b border-[#e0e7ff]">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">
                        <a href="{{ $sortLink }}" class="flex items-center gap-1 group text-[#003f87]">
                            Spécialité
                            <span class="material-symbols-outlined text-[13px]">{{ $sortIcon }}</span>
                        </a>
                    </th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f4ff]">
                @forelse($specialties as $specialty)
                    <tr class="hover:bg-[#f8faff] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-purple-600 text-[16px]">category</span>
                                </div>
                                <span class="text-sm font-semibold text-[#0d1c2f]">{{ $specialty->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#526069]">
                            {{ Str::limit($specialty->description, 60) ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.specialties.edit', $specialty) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Éditer
                                </a>
                                <form method="POST" action="{{ route('admin.specialties.destroy', $specialty) }}" class="inline"
                                      onsubmit="return confirm('Supprimer la spécialité {{ addslashes($specialty->name) }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-[#526069]">
                            <span class="material-symbols-outlined text-4xl text-[#c2c6d4] block mb-2">category</span>
                            Aucune spécialité enregistrée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $specialties->links() }}
</div>
@endsection
