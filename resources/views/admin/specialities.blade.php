@extends('layouts.app')
@section('title', 'Gestion des Spécialités')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center gap-4">
            <a href="/admin/dashboard" class="flex items-center gap-2 text-[#003f87] hover:underline">
                <span class="material-symbols-outlined">arrow_back</span>
                Retour
            </a>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Gestion des Spécialités</h1>
        </div>
        <button
            onclick="document.getElementById('add-form').classList.toggle('hidden')"
            class="flex items-center gap-2 px-6 py-3 bg-[#003f87] text-white rounded-xl font-medium hover:opacity-90 transition-all"
        >
            <span class="material-symbols-outlined">add</span>
            Ajouter Spécialité
        </button>
    </div>

    {{-- Add Form --}}
    <div id="add-form" class="hidden bg-white rounded-2xl p-8 border border-[#c2c6d4]/30 shadow-sm mb-8">
        <h2 class="text-xl font-semibold text-[#0d1c2f] mb-6">Ajouter une spécialité</h2>
        <form action="/admin/specialities" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#0d1c2f]">Nom</label>
                    <input
                        type="text"
                        name="name"
                        placeholder="Cardiologie"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                    />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#0d1c2f]">Description</label>
                    <input
                        type="text"
                        name="description"
                        placeholder="Surveillance et soins cardiaques..."
                        class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                    />
                </div>
            </div>
            <div class="flex gap-4 pt-2">
                <button
                    type="submit"
                    class="px-8 py-3 bg-[#003f87] text-white rounded-xl font-medium hover:opacity-90 transition-all"
                >
                    Ajouter
                </button>
                <button
                    type="button"
                    onclick="document.getElementById('add-form').classList.add('hidden')"
                    class="px-8 py-3 bg-white border border-[#c2c6d4] text-[#424752] rounded-xl font-medium hover:bg-[#f8f9ff] transition-all"
                >
                    Annuler
                </button>
            </div>
        </form>
    </div>

    {{-- Specialities Grid --}}
    @if($specialities->isEmpty())
        <div class="bg-white rounded-xl p-12 border border-[#c2c6d4]/30 shadow-sm text-center text-[#424752]">
            Aucune spécialité — Ajoutez-en une !
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($specialities as $spec)
                <div class="bg-white rounded-xl p-6 border border-[#c2c6d4]/30 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-[#eff4ff] text-[#003f87] flex items-center justify-center">
                            <span class="material-symbols-outlined">medical_services</span>
                        </div>
                        <form action="/admin/specialities/{{ $spec->id }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button
                                onclick="return confirm('Supprimer cette spécialité ?')"
                                class="text-[#ba1a1a] hover:bg-[#ffdad6] p-2 rounded-lg transition-colors"
                            >
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </div>
                    <h3 class="text-lg font-semibold text-[#0d1c2f] mb-2">{{ $spec->name }}</h3>
                    @if($spec->description)
                        <p class="text-sm text-[#424752]">{{ $spec->description }}</p>
                    @endif
                    <div class="mt-4 pt-4 border-t border-[#c2c6d4]">
                        <p class="text-xs text-[#526069]">
                            {{ $spec->doctors->count() ?? 0 }} médecin(s)
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
