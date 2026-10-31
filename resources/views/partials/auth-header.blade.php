<header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-[#e6ebf5]">
    <div class="sc-page-wide h-16 flex items-center justify-between gap-3">
        <a href="{{ route('landing') }}" class="flex items-center gap-2 min-w-0">
            <span class="w-9 h-9 rounded-xl bg-[#0b1220] text-white flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">favorite</span>
            </span>
            <span class="font-bold text-[#0b1220] truncate">SantéConnect</span>
        </a>
        <div class="hidden sm:flex items-center gap-4 text-xs font-semibold text-[#5b6573]">
            <span class="inline-flex items-center gap-1 text-rose-600">
                <span class="material-symbols-outlined text-sm" aria-hidden="true">emergency</span>
                Urgences : 15 / 112
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('login') }}" class="sc-nav-link {{ request()->routeIs('login') ? 'sc-nav-link-active' : '' }}">Connexion</a>
            <a href="{{ route('register') }}" class="sc-btn-primary !min-h-10 !px-3 text-xs sm:text-sm">
                <span class="sm:hidden">Inscription</span>
                <span class="hidden sm:inline">Créer un compte</span>
            </a>
        </div>
    </div>
</header>
