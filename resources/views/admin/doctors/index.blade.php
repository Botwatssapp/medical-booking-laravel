@extends('layouts.admin')
@section('page-title', 'Médecins')
@section('page-subtitle', 'Gestion des médecins enregistrés dans le système')

@section('admin-content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <div></div>
        <a href="{{ route('admin.doctors.create') }}"
           class="flex items-center gap-2 bg-[#003f87] hover:opacity-90 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-opacity">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Ajouter un médecin
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8faff] border-b border-[#e0e7ff]">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Médecin</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Spécialité</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0f4ff]">
                @forelse($doctors as $doctor)
                    <tr class="hover:bg-[#f8faff] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-sm shrink-0 overflow-hidden">
                                    @if($doctor->user->profile_image_url)
                                        <img src="{{ $doctor->user->profile_image_url }}" class="w-full h-full object-cover" alt="">
                                    @else
                                        {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#0d1c2f]">Dr {{ $doctor->user->name }}</p>
                                    <p class="text-xs text-[#526069]">{{ $doctor->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-[#eff4ff] text-[#003f87] rounded-full text-xs font-semibold">
                                {{ $doctor->speciality->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#526069]">
                            {{ $doctor->phone ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.doctors.edit', $doctor) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Éditer
                                </a>
                                <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" class="inline"
                                      onsubmit="return confirm('Supprimer le profil de Dr {{ addslashes($doctor->user->name) }} ?')">
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
                        <td colspan="4" class="px-6 py-12 text-center text-[#526069]">
                            <span class="material-symbols-outlined text-4xl text-[#c2c6d4] block mb-2">stethoscope</span>
                            Aucun médecin enregistré
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $doctors->links() }}
</div>
@endsection
