@extends('layouts.app')
@section('title', 'Connexion')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-[1100px] bg-white rounded-3xl overflow-hidden shadow-sm border border-[#c2c6d4] grid grid-cols-1 md:grid-cols-2">

        {{-- Left --}}
        <div class="relative bg-[#eff4ff] p-12 flex flex-col justify-between overflow-hidden">
            <div class="space-y-6">
                <h1 class="text-3xl font-bold text-[#003f87] leading-tight">
                    Votre santé, simplifiée et connectée.
                </h1>
                <p class="text-[#424752] text-lg leading-relaxed">
                    Accédez à votre dossier médical, gérez vos rendez-vous et communiquez avec vos praticiens en toute sécurité.
                </p>
            </div>
            <div class="mt-8 bg-white/60 rounded-2xl p-6 border border-white/80">
                <div class="flex items-center gap-3 mb-2">
                    <span class="material-symbols-outlined text-[#003f87]">security</span>
                    <span class="text-sm font-semibold text-[#0d1c2f]">Données sécurisées</span>
                </div>
                <p class="text-sm text-[#424752]">Vos informations médicales sont protégées par les plus hauts standards.</p>
            </div>
            <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-[#d7e2ff] opacity-40 rounded-full blur-3xl"></div>
        </div>

        {{-- Right --}}
        <div class="flex flex-col justify-center p-8 md:p-16">
            <div class="max-w-md w-full mx-auto">
                <div class="mb-10">
                    <h2 class="text-2xl font-semibold text-[#0d1c2f] mb-2">Bon retour parmi nous</h2>
                    <p class="text-[#424752]">Veuillez entrer vos identifiants pour accéder à votre espace.</p>
                </div>

                <form action="/login" method="POST" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-[#424752]">Adresse e-mail</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">mail</span>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nom@exemple.com"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-[#f8f9ff] border border-[#c2c6d4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#003f87] text-sm"
                            />
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-sm font-medium text-[#424752]">Mot de passe</label>
                            <a href="#" class="text-sm text-[#003f87] hover:underline">Mot de passe oublié ?</a>
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">lock</span>
                            <input
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-[#f8f9ff] border border-[#c2c6d4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#003f87] text-sm"
                            />
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full bg-[#003f87] text-white py-4 rounded-xl font-semibold hover:opacity-90 transition-all shadow-md"
                    >
                        Se connecter
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-[#424752]">
                    Vous n'avez pas encore de compte ?
                    <a href="/register" class="text-[#003f87] font-semibold hover:underline">Inscrivez-vous</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
