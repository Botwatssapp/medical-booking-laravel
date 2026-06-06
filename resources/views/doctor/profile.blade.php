@extends('layouts.app')
@section('title', 'Mon Profil')

@section('content')
<div class="max-w-[800px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-10">
        <a href="/doctor/dashboard" class="flex items-center gap-2 text-[#003f87] hover:underline">
            <span class="material-symbols-outlined">arrow_back</span>
            Retour
        </a>
        <h1 class="text-3xl font-bold text-[#0d1c2f]">Mon Profil</h1>
    </div>

    <div class="bg-white rounded-2xl p-8 border border-[#c2c6d4]/30 shadow-sm">

        {{-- Doctor Info --}}
        <div class="flex flex-col items-center text-center mb-8 pb-8 border-b border-[#c2c6d4]">
            <div class="w-24 h-24 rounded-full bg-[#0056b3] text-white flex items-center justify-center text-4xl font-bold mb-4">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <h2 class="text-xl font-semibold text-[#0d1c2f]">Dr. {{ Auth::user()->name }}</h2>
            <p class="text-sm text-[#003f87] font-medium mt-1">{{ $doctor->speciality->name }}</p>
            <p class="text-sm text-[#424752] mt-1">{{ Auth::user()->email }}</p>
        </div>

        {{-- Update Form --}}
        <form action="/doctor/profile" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Phone --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Téléphone</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">call</span>
                    <input
                        type="tel"
                        name="phone"
                        value="{{ $doctor->phone }}"
                        placeholder="06 12 34 56 78"
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                    />
                </div>
            </div>

            {{-- Address --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Adresse du cabinet</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">location_on</span>
                    <input
                        type="text"
                        name="address"
                        value="{{ $doctor->address }}"
                        placeholder="123 Rue de la Santé, Paris"
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                    />
                </div>
            </div>

            {{-- Bio --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-[#0d1c2f]">Biographie</label>
                <textarea
                    name="bio"
                    rows="4"
                    placeholder="Décrivez votre parcours et vos spécialités..."
                    class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm resize-none"
                >{{ $doctor->bio }}</textarea>
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="w-full bg-[#003f87] text-white py-4 rounded-xl font-semibold hover:opacity-90 transition-all shadow-md"
            >
                Mettre à jour le profil
            </button>
        </form>
    </div>
</div>
@endsection
