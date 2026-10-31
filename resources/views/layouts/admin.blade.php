@extends('layouts.app')
@section('title')
    @yield('page-title', 'Espace')
@endsection
@section('suppress-global-flash', true)

@section('content')
<div class="min-h-screen bg-[#faf8ff] text-[#131b2e] antialiased"
     x-data="{
        open: false,
        notices: false,
        createOpen: false,
        toast: '',
        showToast(message) {
            this.toast = message;
            window.clearTimeout(this._toastTimer);
            this._toastTimer = window.setTimeout(() => this.toast = '', 3200);
        }
     }"
     @admin-toast.window="showToast($event.detail)"
     @keydown.escape.window="open = false; notices = false; createOpen = false">

    <button type="button"
            class="lg:hidden fixed bottom-5 right-5 z-50 w-12 h-12 rounded-full bg-[#2563eb] text-white shadow-lg flex items-center justify-center"
            @click="open = !open"
            :aria-expanded="open.toString()"
            aria-controls="admin-sidebar"
            :aria-label="open ? 'Fermer le menu administrateur' : 'Ouvrir le menu administrateur'">
        <span class="material-symbols-outlined" aria-hidden="true" x-text="open ? 'close' : 'menu'">menu</span>
    </button>

    <div x-show="open" x-cloak class="lg:hidden fixed inset-0 bg-slate-900/40 z-40" @click="open = false" aria-hidden="true"></div>

    @php
        $isActive = fn (string $pattern) => request()->routeIs($pattern);
        $navCls = fn (string $pattern) => $isActive($pattern)
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all bg-[#2563eb] text-white font-semibold shadow-sm'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#434655] hover:bg-[#e2e7ff] hover:text-[#131b2e] transition-all';
        $pendingCount = \App\Models\User::doctors()->doesntHave('doctor')->count();
    @endphp

    <aside id="admin-sidebar"
           class="fixed left-0 top-0 h-full w-64 bg-[#f2f3ff] z-50 flex flex-col py-5 overflow-y-auto shadow-[0_1px_8px_rgba(15,23,42,0.04)]
                  transform transition-transform duration-200 -translate-x-full lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           aria-label="Navigation administrateur">

        <div class="px-5 mb-6 flex items-center justify-between gap-2">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 min-w-0">
                <span class="w-8 h-8 rounded-lg bg-[#004ac6] text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">favorite</span>
                </span>
                <span class="font-display text-[16px] leading-6 font-bold text-[#004ac6] truncate">SantéConnect</span>
            </a>
            <button type="button" class="lg:hidden p-1 rounded-lg text-[#434655]" @click="open = false" aria-label="Fermer le menu">
                <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
            </button>
        </div>

        <nav class="flex-1 px-4 space-y-1">
            <a href="{{ route('admin.doctors.index') }}" class="{{ $navCls('admin.doctors.*') }}" @click="open = false" @if($isActive('admin.doctors.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span>
                <span class="text-[14px] leading-5 font-semibold whitespace-nowrap">Médecins</span>
            </a>
            <a href="{{ route('admin.specialties.index') }}" class="{{ $navCls('admin.specialties.*') }}" @click="open = false" @if($isActive('admin.specialties.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">category</span>
                <span class="text-[14px] leading-5 font-semibold whitespace-nowrap">Spécialités</span>
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="{{ $navCls('admin.appointments.*') }}" @click="open = false" @if($isActive('admin.appointments.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">calendar_month</span>
                <span class="text-[14px] leading-5 font-semibold whitespace-nowrap">Rendez-vous</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="{{ $navCls('admin.users.*') }}" @click="open = false" @if($isActive('admin.users.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">pending_actions</span>
                <span class="text-[14px] leading-5 font-semibold whitespace-nowrap">Utilisateurs</span>
                @if($pendingCount > 0)
                    <span class="ml-auto min-w-[20px] h-5 px-1 bg-amber-400 text-[#131b2e] text-[11px] font-bold rounded-full flex items-center justify-center">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.dashboard') }}" class="{{ $navCls('admin.dashboard') }}" @click="open = false" @if($isActive('admin.dashboard')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">admin_panel_settings</span>
                <span class="text-[14px] leading-5 font-semibold whitespace-nowrap">Tableau de bord</span>
            </a>
            <a href="{{ route('admin.profile.edit') }}" class="{{ $navCls('admin.profile.*') }}" @click="open = false" @if($isActive('admin.profile.*')) aria-current="page" @endif>
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">manage_accounts</span>
                <span class="text-[14px] leading-5 font-semibold whitespace-nowrap">Mon profil</span>
            </a>
        </nav>

        <div class="px-4 pt-4 mt-auto space-y-3">
            <div class="p-4 bg-white rounded-xl shadow-[0_1px_3px_rgba(15,23,42,0.04)]">
                <div class="flex items-center gap-1 text-[#ba1a1a] font-semibold text-[11px] mb-1">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">e911_emergency</span>
                    Urgences
                </div>
                <p class="text-[11px] font-bold text-[#131b2e]">Appelez le 15 ou le 112</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="px-1 pb-2">
                @csrf
                <button type="submit" class="text-[14px] font-semibold text-[#434655] hover:text-[#131b2e]">Déconnexion</button>
            </form>
        </div>
    </aside>

    <div class="lg:pl-64 min-w-0">
        <header class="sc-appbar sticky lg:fixed top-0 left-0 lg:left-64 right-0 min-h-16 lg:min-h-[4.5rem] bg-white/90 backdrop-blur-md shadow-[0_1px_8px_rgba(15,23,42,0.04)] z-40 px-4 sm:px-6 lg:px-8 py-3">
            <div class="sc-appbar__title">
                @unless(request()->routeIs('admin.dashboard'))
                    <h1 class="font-display">@yield('page-title', 'Tableau de bord')</h1>
                    <p>@yield('page-subtitle', 'Panneau de contrôle SantéConnect')</p>
                @else
                    <div class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full bg-[#ffdad6] text-[#93000a]">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">emergency</span>
                        <span class="text-[11px] font-semibold">Urgences : 15 / 112</span>
                    </div>
                @endunless
            </div>
            <div class="sc-appbar__actions">
                @yield('page-actions')
                <div class="relative">
                    <button type="button"
                            class="relative p-2 rounded-full text-[#434655] hover:bg-[#e2e7ff] hover:text-[#131b2e]"
                            @click="notices = !notices"
                            :aria-expanded="notices.toString()"
                            aria-label="Notifications administrateur">
                        <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                        @if($pendingCount > 0)
                            <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#ba1a1a] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#ba1a1a]"></span>
                            </span>
                        @endif
                    </button>
                    <div x-show="notices" x-cloak x-transition
                         class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-lg p-4 z-20"
                         @click.outside="notices = false">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#434655] mb-2">File de validation</p>
                        @if($pendingCount > 0)
                            <p class="text-sm text-[#131b2e]">{{ $pendingCount }} médecin(s) en attente de profil.</p>
                            <a href="{{ route('admin.doctors.create') }}" class="mt-2 inline-flex text-sm font-semibold text-[#2563eb]">Ouvrir la validation</a>
                        @else
                            <p class="text-sm text-[#434655]">Aucune demande médecin en attente.</p>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 min-w-0" aria-label="Mon profil">
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full overflow-hidden bg-[#004ac6] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#2563eb]">
                            @if(Auth::user()->profile_image_url)
                                <img src="{{ Auth::user()->profile_image_url }}" alt="Photo de {{ Auth::user()->name }}" class="w-full h-full object-cover">
                            @else
                                <span aria-hidden="true">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-[#007d55] ring-2 ring-white"></span>
                    </div>
                    <span class="hidden md:flex flex-col text-left leading-tight">
                        <span class="text-[14px] font-semibold truncate max-w-[140px]">{{ Auth::user()->name }}</span>
                        <span class="text-[11px] text-[#006242] font-medium">Administrateur</span>
                    </span>
                </a>
            </div>
        </header>

        <main class="relative lg:pt-[4.5rem] bg-[#faf8ff] min-h-screen">
            @include('components.ui.flash', ['wrapperClass' => 'sc-page-wide mt-6'])
            <div class="sc-page-wide py-6 sm:py-8 lg:py-10 pb-24 lg:pb-10 min-w-0 overflow-x-hidden">
                @yield('admin-content')
            </div>
        </main>
    </div>

    <div x-show="toast" x-cloak x-transition class="fixed bottom-5 left-1/2 -translate-x-1/2 z-[70] max-w-md w-[calc(100%-2rem)]" role="status">
        <div class="rounded-2xl bg-[#131b2e] text-white px-4 py-3 text-sm shadow-2xl text-center" x-text="toast"></div>
    </div>
</div>
@endsection
