@extends('layouts.app')
@section('suppress-global-flash', true)

@section('content')
<div class="flex min-h-[calc(100vh-5rem)] bg-[#f0f4ff]" x-data="{ open: false }" @keydown.escape.window="open = false">

    <button type="button"
            class="lg:hidden fixed bottom-5 right-5 z-40 w-12 h-12 rounded-full bg-[#003f87] text-white shadow-lg flex items-center justify-center"
            @click="open = !open"
            :aria-expanded="open.toString()"
            aria-controls="admin-sidebar"
            :aria-label="open ? 'Fermer le menu administrateur' : 'Ouvrir le menu administrateur'">
        <span class="material-symbols-outlined" aria-hidden="true" x-text="open ? 'close' : 'menu'">menu</span>
    </button>

    <div x-show="open"
         x-cloak
         class="lg:hidden fixed inset-0 bg-black/40 z-30"
         @click="open = false"
         aria-hidden="true"></div>

    <aside id="admin-sidebar"
           class="w-64 shrink-0 bg-[#0d1c2f] text-white flex flex-col min-h-screen
                  fixed lg:sticky top-0 lg:top-20 inset-y-0 left-0 z-40
                  transform transition-transform duration-200
                  -translate-x-full lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           aria-label="Navigation administrateur">

        <div class="px-6 py-5 border-b border-white/10 flex items-start justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-[#003f87] flex items-center justify-center shrink-0 overflow-hidden">
                    @if(Auth::user()->profile_image_url)
                        <img src="{{ Auth::user()->profile_image_url }}"
                             alt="Photo de {{ Auth::user()->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-white text-xl" aria-hidden="true">shield_person</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] text-[#7b9fd4] font-medium uppercase tracking-wider">Administrateur</p>
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
            $isActive = fn (string $pattern) => request()->routeIs($pattern);
            $navCls = fn (string $pattern) => $isActive($pattern)
                ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold bg-[#003f87] text-white shadow-sm'
                : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#8fa8c8] hover:bg-white/10 hover:text-white transition-colors';
            $pendingCount = \App\Models\User::doctors()->doesntHave('doctor')->count();
        @endphp

        <nav class="flex-1 px-3 py-4 space-y-0.5">
            <a href="{{ route('admin.dashboard') }}" class="{{ $navCls('admin.dashboard') }}" @if($isActive('admin.dashboard')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">dashboard</span>
                Tableau de bord
            </a>

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#4a6580]">Utilisateurs</p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="{{ $navCls('admin.users.*') }}" @if($isActive('admin.users.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">group</span>
                Utilisateurs
                @if($pendingCount > 0)
                    <span class="ml-auto min-w-[20px] h-5 px-1 bg-amber-400 text-[#0d1c2f] text-[11px] font-bold rounded-full flex items-center justify-center"
                          aria-label="{{ $pendingCount }} médecin(s) en attente">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.doctors.index') }}" class="{{ $navCls('admin.doctors.*') }}" @if($isActive('admin.doctors.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">stethoscope</span>
                Médecins
            </a>

            <a href="{{ route('admin.specialties.index') }}" class="{{ $navCls('admin.specialties.*') }}" @if($isActive('admin.specialties.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">category</span>
                Spécialités
            </a>

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#4a6580]">Système</p>
            </div>

            <a href="{{ route('admin.appointments.index') }}" class="{{ $navCls('admin.appointments.*') }}" @if($isActive('admin.appointments.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_month</span>
                Rendez-vous
            </a>

            <a href="{{ route('admin.profile.edit') }}" class="{{ $navCls('admin.profile.*') }}" @if($isActive('admin.profile.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_accounts</span>
                Mon profil
            </a>
        </nav>

        <div class="px-6 py-4 border-t border-white/10">
            <p class="text-[10px] text-[#4a6580]">SantéConnect · Admin</p>
        </div>
    </aside>

    <div class="flex-1 min-w-0 lg:ml-0">
        <div class="bg-white border-b border-[#e0e7ff] px-4 sm:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-16 sm:top-20 z-10 shadow-sm">
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-[#0d1c2f] truncate">@yield('page-title', 'Tableau de bord')</h1>
                <p class="text-xs text-[#526069] mt-0.5">@yield('page-subtitle', 'Panneau de contrôle SantéConnect')</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                @if($pendingCount > 0)
                    <a href="{{ route('admin.doctors.create') }}"
                       class="flex items-center gap-1.5 bg-amber-50 border border-amber-300 text-amber-900 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-amber-100 transition-colors">
                        <span class="material-symbols-outlined text-sm" aria-hidden="true">pending</span>
                        {{ $pendingCount }} en attente
                    </a>
                @endif
                @yield('page-actions')
            </div>
        </div>

        @include('components.ui.flash', ['wrapperClass' => 'mx-4 sm:mx-8 mt-5'])

        <div class="p-4 sm:p-8">
            @yield('admin-content')
        </div>
    </div>
</div>
@endsection
