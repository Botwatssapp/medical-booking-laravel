@extends('layouts.admin')
@section('page-title', 'Ajouter une spécialité')
@section('page-subtitle', 'Référentiel utilisé pour classer les médecins')

@section('admin-content')
<div class="max-w-2xl space-y-5">

    <a href="{{ route('admin.specialties.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-[#526069] hover:text-[#003f87]">
        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
        Retour aux spécialités
    </a>

    <form method="POST" action="{{ route('admin.specialties.store') }}"
          class="bg-white rounded-2xl border border-[#e0e7ff] shadow-sm p-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Nom</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                   class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3">{{ old('description') }}</textarea>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" class="px-6 py-3 bg-[#003f87] text-white rounded-xl text-sm font-semibold">Créer</button>
            <a href="{{ route('admin.specialties.index') }}" class="px-6 py-3 border border-[#c2c6d4] rounded-xl text-sm font-semibold text-[#526069] text-center">Annuler</a>
        </div>
    </form>
</div>
@endsection
