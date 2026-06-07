@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-[#f0f4ff]">

    {{-- ── Sidebar médecin ── --}}
    <aside class="w-60 shrink-0 bg-[#0d1c2f] text-white flex flex-col min-h-screen sticky top-20">

        {{-- Avatar + nom --}}
        <div class="px-5 py-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 bg-[#003f87] flex items-center justify-center">
                    @if(Auth::user()->profile_image_url)
                        <img src="{{ Auth::user()->profile_image_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <span class="text-white font-bold text-base">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white truncate">Dr. {{ Auth::user()->name }}</p>
                    @if(Auth::user()->doctor)
                        <p class="text-[11px] text-[#7b9fd4] truncate">{{ Auth::user()->doctor->speciality->name ?? '' }}</p>
                    @else
                        <p class="text-[11px] text-amber-400">En attente de validation</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        @php
            $navCls = fn(string $pattern) => request()->routeIs($pattern)
                ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold bg-[#003f87] text-white shadow-sm'
                : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#8fa8c8] hover:bg-white/10 hover:text-white transition-colors';
        @endphp

        <nav class="flex-1 px-3 py-4 space-y-0.5">

            <a href="{{ route('doctor.dashboard') }}" class="{{ $navCls('doctor.dashboard') }}">
                <span class="material-symbols-outlined text-[20px]">home</span>
                Tableau de bord
            </a>

            <a href="{{ route('doctor.appointments.index') }}" class="{{ $navCls('doctor.appointments.*') }}">
                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                Rendez-vous
                @php $pending = Auth::user()->doctor?->appointments()->pending()->count() ?? 0; @endphp
                @if($pending > 0)
                    <span class="ml-auto min-w-[20px] h-5 px-1 bg-yellow-400 text-[#0d1c2f] text-[11px] font-bold rounded-full flex items-center justify-center">
                        {{ $pending }}
                    </span>
                @endif
            </a>

            <a href="{{ route('doctor.availabilities.index') }}" class="{{ $navCls('doctor.availabilities.*') }}">
                <span class="material-symbols-outlined text-[20px]">schedule</span>
                Disponibilités
            </a>

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#4a6580]">Compte</p>
            </div>

            <a href="{{ route('doctor.profile.edit') }}" class="{{ $navCls('doctor.profile.*') }}">
                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                Mon profil
            </a>
        </nav>

        <div class="px-5 py-4 border-t border-white/10">
            <p class="text-[10px] text-[#4a6580]">SantéConnect · Médecin</p>
        </div>
    </aside>

    {{-- ── Contenu principal ── --}}
    <div class="flex-1 min-w-0">

        {{-- Barre de titre --}}
        <div class="bg-white border-b border-[#e0e7ff] px-8 py-4 flex items-center justify-between sticky top-20 z-10 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-[#0d1c2f]">@yield('page-title', 'Tableau de bord')</h1>
                <p class="text-xs text-[#526069] mt-0.5">@yield('page-subtitle', 'Bienvenue sur votre espace médecin')</p>
            </div>
            <div class="flex items-center gap-3">
                @yield('page-actions')
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mx-8 mt-5">
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3.5 text-sm font-medium">
                    <span class="material-symbols-outlined text-green-600 shrink-0">check_circle</span>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if(session('warning'))
            <div class="mx-8 mt-5">
                <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3.5 text-sm font-medium">
                    <span class="material-symbols-outlined text-amber-600 shrink-0">warning</span>
                    {{ session('warning') }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="mx-8 mt-5">
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3.5 text-sm font-medium">
                    <span class="material-symbols-outlined text-red-600 shrink-0">error</span>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-8 mt-5">
                <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Contenu de la page --}}
        <div class="p-8">
            @yield('doctor-content')
        </div>
    </div>
</div>
@endsection
