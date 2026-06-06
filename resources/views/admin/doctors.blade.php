@extends('layouts.app')
@section('title', 'Gestion des Médecins')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center gap-4">
            <a href="/admin/dashboard" class="flex items-center gap-2 text-[#003f87] hover:underline">
                <span class="material-symbols-outlined">arrow_back</span>
                Retour
            </a>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Gestion des Médecins</h1>
        </div>
        <button
            onclick="document.getElementById('add-doctor-form').classList.toggle('hidden')"
            class="flex items-center gap-2 px-6 py-3 bg-[#003f87] text-white rounded-xl font-medium hover:opacity-90 transition-all"
        >
            <span class="material-symbols-outlined">add</span>
            Ajouter Médecin
        </button>
    </div>

    {{-- Add Doctor Form --}}
    <div id="add-doctor-form" class="hidden bg-white rounded-2xl p-8 border border-[#c2c6d4]/30 shadow-sm mb-8">
        <h2 class="text-xl font-semibold text-[#0d1c2f] mb-6">Ajouter un nouveau médecin</h2>
        <form action="/admin/doctors" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Nom complet</label>
                <input
                    type="text"
                    name="name"
                    placeholder="Dr. Jean Dupont"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Email</label>
                <input
                    type="email"
                    name="email"
                    placeholder="doctor@example.com"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Mot de passe</label>
                <input
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Spécialité</label>
                <select
                    name="speciality_id"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                >
                    <option value="">Choisir une spécialité</option>
                    @foreach($doctors->pluck('speciality')->unique('id') as $spec)
                        @if($spec)
                            <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Téléphone</label>
                <input
                    type="tel"
                    name="phone"
                    placeholder="06 12 34 56 78"
                    class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Adresse</label>
                <input
                    type="text"
                    name="address"
                    placeholder="123 Rue de la Santé, Paris"
                    class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                />
            </div>
            <div class="md:col-span-2 flex gap-4">
                <button
                    type="submit"
                    class="px-8 py-3 bg-[#003f87] text-white rounded-xl font-medium hover:opacity-90 transition-all"
                >
                    Ajouter
                </button>
                <button
                    type="button"
                    onclick="document.getElementById('add-doctor-form').classList.add('hidden')"
                    class="px-8 py-3 bg-white border border-[#c2c6d4] text-[#424752] rounded-xl font-medium hover:bg-[#f8f9ff] transition-all"
                >
                    Annuler
                </button>
            </div>
        </form>
    </div>

    {{-- Doctors Table --}}
    <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#f8f9ff] border-b border-[#c2c6d4]">
                <tr>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Médecin</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Spécialité</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Téléphone</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Adresse</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-[#526069] uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#c2c6d4]/30">
                @forelse($doctors as $doctor)
                    <tr class="hover:bg-[#f8f9ff] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#0056b3] text-white flex items-center justify-center text-sm font-bold">
                                    {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#0d1c2f]">Dr. {{ $doctor->user->name }}</p>
                                    <p class="text-xs text-[#526069]">{{ $doctor->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-[#eff4ff] text-[#003f87]">
                                {{ $doctor->speciality->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ $doctor->phone ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-[#424752]">{{ $doctor->address ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <form action="/admin/doctors/{{ $doctor->id }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    onclick="return confirm('Supprimer ce médecin ?')"
                                    class="px-3 py-1.5 border border-[#ba1a1a] text-[#ba1a1a] rounded-lg text-xs font-medium hover:bg-[#ffdad6] transition-colors"
                                >
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#424752]">Aucun médecin</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-[#c2c6d4]">
            {{ $doctors->links() }}
        </div>
    </div>
</div>
@endsection
