@extends('layouts.admin')
@section('page-title', 'Médecins')
@section('page-subtitle', 'Gestion des médecins enregistrés dans le système')

@section('admin-content')
<div class="space-y-6">

    @if($pendingDoctors->isNotEmpty())
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5">
            <p class="font-bold text-amber-900 mb-3">{{ $pendingDoctors->count() }} compte{{ $pendingDoctors->count() > 1 ? 's' : '' }} médecin à valider</p>
            <div class="space-y-2">
                @foreach($pendingDoctors as $pending)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-xl px-4 py-3 border border-amber-200">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $pending->name }}</p>
                            <p class="text-xs text-gray-500">{{ $pending->email }} · inscrit le {{ $pending->created_at->format('d/m/Y') }}</p>
                        </div>
                        <a href="{{ route('admin.doctors.create', ['user_id' => $pending->id]) }}"
                           class="inline-flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">
                            Confirmer le compte
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex justify-between items-center">
        <div></div>
        <a href="{{ route('admin.doctors.create') }}"
           class="flex items-center gap-2 bg-[#003f87] hover:opacity-90 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-opacity">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Ajouter un médecin
        </a>
    </div>

    @php
        $sortCol = request('sort', 'name');
        $sortDir = request('direction', 'asc');
        $sortLink = fn($col) => request()->fullUrlWithQuery(['sort' => $col, 'direction' => ($sortCol === $col && $sortDir === 'asc') ? 'desc' : 'asc', 'page' => 1]);
        $sortIcon = fn($col) => $sortCol === $col ? ($sortDir === 'asc' ? 'arrow_upward' : 'arrow_downward') : 'unfold_more';
    @endphp
    <div class="bg-white rounded-2xl border border-[#e0e7ff] overflow-x-auto">
        <table class="w-full min-w-[680px]">
            <thead class="bg-[#f8faff] border-b border-[#e0e7ff]">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">
                        <a href="{{ $sortLink('name') }}" class="flex items-center gap-1 group {{ $sortCol === 'name' ? 'text-[#003f87]' : 'hover:text-[#003f87]' }}">
                            Médecin
                            <span class="material-symbols-outlined text-[13px] {{ $sortCol === 'name' ? '' : 'opacity-30 group-hover:opacity-70' }}">{{ $sortIcon('name') }}</span>
                        </a>
                    </th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">
                        <a href="{{ $sortLink('speciality') }}" class="flex items-center gap-1 group {{ $sortCol === 'speciality' ? 'text-[#003f87]' : 'hover:text-[#003f87]' }}">
                            Spécialité
                            <span class="material-symbols-outlined text-[13px] {{ $sortCol === 'speciality' ? '' : 'opacity-30 group-hover:opacity-70' }}">{{ $sortIcon('speciality') }}</span>
                        </a>
                    </th>
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
                                    @if($doctor->photo)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($doctor->photo) }}" class="w-full h-full object-cover" alt="Photo de cabinet de Dr {{ $doctor->user->name }}">
                                    @elseif($doctor->user->profile_image_url)
                                        <img src="{{ $doctor->user->profile_image_url }}" class="w-full h-full object-cover" alt="Photo de Dr {{ $doctor->user->name }}">
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
                            <span class="material-symbols-outlined text-4xl text-[#c2c6d4] block mb-2" aria-hidden="true">stethoscope</span>
                            <p class="font-semibold text-[#0d1c2f]">Aucun médecin validé</p>
                            <p class="text-sm mt-1 mb-4">Validez un compte médecin inscrit pour le rendre réservable.</p>
                            <a href="{{ route('admin.doctors.create') }}" class="inline-flex items-center px-4 py-2 bg-[#003f87] text-white rounded-xl text-sm font-semibold">Valider un médecin</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $doctors->links() }}
</div>
@endsection
