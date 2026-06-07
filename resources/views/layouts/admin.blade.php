@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-[#f0f4ff]">

    {{-- ── Sidebar ── --}}
    <aside class="w-64 shrink-0 bg-[#0d1c2f] text-white flex flex-col min-h-screen sticky top-20">

        {{-- Admin badge --}}
        <div class="px-6 py-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#003f87] flex items-center justify-center shrink-0 overflow-hidden">
                    @if(Auth::user()->profile_image_url)
                        <img src="{{ Auth::user()->profile_image_url }}"
                             alt="Admin" class="w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-white text-xl">shield_person</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] text-[#7b9fd4] font-medium uppercase tracking-wider">Administrateur</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5">

            @php
            $isActive = fn(string $pattern) => request()->routeIs($pattern);
            $navCls = fn(string $pattern) => $isActive($pattern)
                ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold bg-[#003f87] text-white shadow-sm'
                : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#8fa8c8] hover:bg-white/10 hover:text-white transition-colors';
            @endphp

            <a href="{{ route('admin.dashboard') }}" class="{{ $navCls('admin.dashboard') }}">
                <span class="material-symbols-outlined text-[20px]">dashboard</span>
                Tableau de bord
            </a>

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#4a6580]">Utilisateurs</p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="{{ $navCls('admin.users.*') }}">
                <span class="material-symbols-outlined text-[20px]">group</span>
                Utilisateurs
                @php $pendingCount = \App\Models\User::doctors()->doesntHave('doctor')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="ml-auto min-w-[20px] h-5 px-1 bg-amber-400 text-[#0d1c2f] text-[11px] font-bold rounded-full flex items-center justify-center">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.doctors.index') }}" class="{{ $navCls('admin.doctors.*') }}">
                <span class="material-symbols-outlined text-[20px]">stethoscope</span>
                Médecins
            </a>

            <a href="{{ route('admin.specialties.index') }}" class="{{ $navCls('admin.specialties.*') }}">
                <span class="material-symbols-outlined text-[20px]">category</span>
                Spécialités
            </a>

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#4a6580]">Système</p>
            </div>

            <a href="{{ route('admin.appointments.index') }}" class="{{ $navCls('admin.appointments.*') }}">
                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                Rendez-vous
            </a>

            <a href="{{ route('admin.profile.edit') }}" class="{{ $navCls('admin.profile.*') }}">
                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                Mon profil
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-white/10">
            <p class="text-[10px] text-[#4a6580]">SantéConnect · Admin Panel</p>
        </div>
    </aside>

    {{-- ── Main content ── --}}
    <div class="flex-1 min-w-0">

        {{-- Top bar --}}
        <div class="bg-white border-b border-[#e0e7ff] px-8 py-4 flex items-center justify-between sticky top-20 z-10 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-[#0d1c2f]">@yield('page-title', 'Tableau de bord')</h1>
                <p class="text-xs text-[#526069] mt-0.5">@yield('page-subtitle', 'Panneau de contrôle SantéConnect')</p>
            </div>
            <div class="flex items-center gap-3">
                @if(isset($pendingCount) && $pendingCount > 0)
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-1.5 bg-amber-50 border border-amber-300 text-amber-800 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-amber-100 transition-colors">
                        <span class="material-symbols-outlined text-sm">pending</span>
                        {{ $pendingCount }} en attente
                    </a>
                @endif
                <span class="text-xs text-[#526069] bg-[#f0f4ff] px-3 py-1.5 rounded-lg border border-[#e0e7ff]">
                    {{ now()->isoFormat('dddd D MMMM Y') }}
                </span>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mx-8 mt-5">
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3.5 text-sm font-medium">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-8 mt-5">
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3.5 text-sm font-medium">
                    <span class="material-symbols-outlined text-red-600">error</span>
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

        {{-- Page content --}}
        <div class="p-8">
            @yield('admin-content')
        </div>
    </div>
</div>
@endsection
