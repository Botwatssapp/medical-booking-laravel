@extends('layouts.app')
@section('suppress-global-flash', true)

@section('content')
<div class="flex min-h-[calc(100vh-5rem)] bg-[#f0f4ff]" x-data="{ open: false }" @keydown.escape.window="open = false">

    <button type="button"
            class="lg:hidden fixed bottom-5 right-5 z-40 w-12 h-12 rounded-full bg-[#003f87] text-white shadow-lg flex items-center justify-center"
            @click="open = !open"
            :aria-expanded="open.toString()"
            aria-controls="doctor-sidebar"
            :aria-label="open ? 'Fermer le menu médecin' : 'Ouvrir le menu médecin'">
        <span class="material-symbols-outlined" aria-hidden="true" x-text="open ? 'close' : 'menu'">menu</span>
    </button>

    <div x-show="open"
         x-cloak
         class="lg:hidden fixed inset-0 bg-black/40 z-30"
         @click="open = false"
         aria-hidden="true"></div>

    <aside id="doctor-sidebar"
           class="w-60 shrink-0 bg-[#0d1c2f] text-white flex flex-col min-h-screen
                  fixed lg:sticky top-0 lg:top-20 inset-y-0 left-0 z-40
                  transform transition-transform duration-200
                  -translate-x-full lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           aria-label="Navigation médecin">

        <div class="px-5 py-5 border-b border-white/10 flex items-start justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 bg-[#003f87] flex items-center justify-center">
                    @if(Auth::user()->profile_image_url)
                        <img src="{{ Auth::user()->profile_image_url }}" alt="Photo de Dr. {{ Auth::user()->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-white font-bold text-base" aria-hidden="true">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white truncate">Dr. {{ Auth::user()->name }}</p>
                    @if(Auth::user()->hasDoctorProfile())
                        <p class="text-[11px] text-[#7b9fd4] truncate">{{ Auth::user()->doctor->speciality->name ?? '' }}</p>
                    @else
                        <p class="text-[11px] text-amber-300">En attente de validation</p>
                    @endif
                </div>
            </div>
            <button type="button"
                    class="lg:hidden p-1 rounded-lg text-white/70 hover:text-white hover:bg-white/10"
                    @click="open = false"
                    aria-label="Fermer le menu">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
            </button>
        </div>

        @php
            $navCls = fn (string $pattern) => request()->routeIs($pattern)
                ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold bg-[#003f87] text-white shadow-sm'
                : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#8fa8c8] hover:bg-white/10 hover:text-white transition-colors';
            $isOperational = Auth::user()->hasDoctorProfile();
        @endphp

        <nav class="flex-1 px-3 py-4 space-y-0.5">
            <a href="{{ route('doctor.dashboard') }}" class="{{ $navCls('doctor.dashboard') }}" @if(request()->routeIs('doctor.dashboard')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">home</span>
                Tableau de bord
            </a>

            @if($isOperational)
                <a href="{{ route('doctor.appointments.index') }}" class="{{ $navCls('doctor.appointments.*') }}" @if(request()->routeIs('doctor.appointments.*')) aria-current="page" @endif>
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_month</span>
                    Rendez-vous
                    @php $pending = Auth::user()->doctor->appointments()->pending()->count(); @endphp
                    @if($pending > 0)
                        <span class="ml-auto min-w-[20px] h-5 px-1 bg-yellow-400 text-[#0d1c2f] text-[11px] font-bold rounded-full flex items-center justify-center"
                              aria-label="{{ $pending }} demande(s) en attente">
                            {{ $pending }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('doctor.availabilities.index') }}" class="{{ $navCls('doctor.availabilities.*') }}" @if(request()->routeIs('doctor.availabilities.*')) aria-current="page" @endif>
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">schedule</span>
                    Disponibilités
                </a>
            @endif

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#4a6580]">Compte</p>
            </div>

            <a href="{{ route('doctor.profile.edit') }}" class="{{ $navCls('doctor.profile.*') }}" @if(request()->routeIs('doctor.profile.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_accounts</span>
                Mon profil
            </a>
        </nav>

        <div class="px-5 py-4 border-t border-white/10">
            <p class="text-[10px] text-[#4a6580]">SantéConnect · Médecin</p>
        </div>
    </aside>

    <div class="flex-1 min-w-0 lg:ml-0">
        <div class="bg-white border-b border-[#e0e7ff] px-4 sm:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-16 sm:top-20 z-10 shadow-sm">
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-[#0d1c2f] truncate">@yield('page-title', 'Tableau de bord')</h1>
                <p class="text-xs text-[#526069] mt-0.5">@yield('page-subtitle', 'Bienvenue sur votre espace médecin')</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                @yield('page-actions')
            </div>
        </div>

        @include('components.ui.flash', ['wrapperClass' => 'mx-4 sm:mx-8 mt-5'])

        <div class="p-4 sm:p-8">
            @yield('doctor-content')
        </div>
    </div>
</div>
@endsection
