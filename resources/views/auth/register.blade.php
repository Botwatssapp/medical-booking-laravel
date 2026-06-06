@extends('layouts.app')
@section('title', 'Inscription')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-[1100px] bg-white rounded-3xl overflow-hidden shadow-sm border border-[#c2c6d4] grid grid-cols-1 lg:grid-cols-2">

        {{-- Left — Form --}}
        <div class="p-10 md:p-14">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[#0d1c2f] mb-2">Créer votre compte</h1>
                <p class="text-[#424752]">Rejoignez SantéConnect pour une gestion simplifiée de votre santé.</p>
            </div>

            <form action="/register" method="POST" class="space-y-5">
                @csrf

                {{-- Nom --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#0d1c2f]">Nom complet</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Jean Dupont"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                    />
                </div>

                {{-- Email --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#0d1c2f]">Adresse e-mail</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">mail</span>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="jean.dupont@email.com"
                            required
                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                        />
                    </div>
                </div>

                {{-- Password --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#0d1c2f]">Mot de passe</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">lock</span>
                        <input
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                        />
                    </div>
                    <p class="text-xs text-[#424752]">Minimum 8 caractères.</p>
                </div>

                {{-- Confirm Password --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#0d1c2f]">Confirmer le mot de passe</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">lock</span>
                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="••••••••"
                            required
                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                        />
                    </div>
                </div>

                {{-- Role --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#0d1c2f]">Je suis</label>
                    <select
                        name="role"
                        class="w-full px-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                    >
                        <option value="patient" {{ old('role') == 'patient' ? 'selected' : '' }}>Patient</option>
                        <option value="doctor"  {{ old('role') == 'doctor'  ? 'selected' : '' }}>Médecin</option>
                    </select>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-[#0056b3] text-white py-4 rounded-xl text-lg font-semibold hover:bg-[#003f87] transition-all shadow-sm"
                >
                    Créer mon compte
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-[#c2c6d4] text-center">
                <p class="text-sm text-[#424752]">
                    Déjà inscrit ?
                    <a href="/login" class="text-[#003f87] font-bold hover:underline">Connectez-vous ici</a>
                </p>
            </div>
        </div>

        {{-- Right --}}
        <div class="hidden lg:flex relative bg-[#e6eeff] overflow-hidden items-center justify-center p-12">
            <div class="relative z-10 w-full text-center space-y-8">
                <h2 class="text-3xl font-bold text-[#003f87]">Votre santé, simplifiée.</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 text-left p-4 rounded-lg bg-white/50 border border-white shadow-sm">
                        <span class="material-symbols-outlined text-[#003f87] p-2 bg-[#d7e2ff] rounded-lg">event_available</span>
                        <div>
                            <p class="text-sm font-bold text-[#0d1c2f]">Rendez-vous en 1 clic</p>
                            <p class="text-xs text-[#424752]">Accédez aux disponibilités de vos praticiens en temps réel.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-left p-4 rounded-lg bg-white/50 border border-white shadow-sm">
                        <span class="material-symbols-outlined text-[#003f87] p-2 bg-[#d7e2ff] rounded-lg">security</span>
                        <div>
                            <p class="text-sm font-bold text-[#0d1c2f]">Données sécurisées</p>
                            <p class="text-xs text-[#424752]">Vos informations médicales sont protégées par les plus hauts standards.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-[#acc7ff] rounded-full blur-3xl opacity-30"></div>
        </div>

    </div>
</div>
@endsection
