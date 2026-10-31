@extends('layouts.app')
@section('title', 'Prendre rendez-vous en ligne')
@section('meta')
    <meta name="description" content="SantéConnect permet de consulter l’annuaire des médecins validés et de demander un créneau réel. La téléconsultation n’est pas gérée.">
@endsection

@php
    $isPatient = auth()->check() && auth()->user()->isPatient();
    $searchAction = $isPatient ? route('patient.doctors.index') : route('login');
    $iconFor = function (string $name): string {
        $key = mb_strtolower($name);

        return match (true) {
            str_contains($key, 'cardio') => 'cardiology',
            str_contains($key, 'derma') => 'dermatology',
            str_contains($key, 'pédiat') || str_contains($key, 'pediat') => 'child_care',
            str_contains($key, 'neuro') => 'neurology',
            str_contains($key, 'ortho') => 'orthopedics',
            str_contains($key, 'gyné') || str_contains($key, 'gyne') => 'pregnancy',
            str_contains($key, 'ophtal') => 'visibility',
            str_contains($key, 'dent') => 'dentistry',
            str_contains($key, 'psych') => 'psychology',
            default => 'medical_services',
        };
    };
    $iconTone = [
        ['bg-[#ffdad6] text-[#ba1a1a]', 'cardiology'],
        ['bg-[#89f5e7] text-[#005049]', 'dermatology'],
        ['bg-[#6ffbbe] text-[#005236]', 'child_care'],
        ['bg-[#dbe1ff] text-[#003ea8]', 'clinical_notes'],
        ['bg-[#dae2fd] text-[#004ac6]', 'neurology'],
        ['bg-[#e2e7ff] text-[#006a61]', 'orthopedics'],
    ];
@endphp

@section('content')
<div class="bg-[#faf8ff] text-[#131b2e] antialiased min-h-screen overflow-x-clip" x-data="{ landingNav: false }" @keydown.escape.window="landingNav = false">
    <header class="fixed top-0 w-full z-50 bg-[#faf8ff]/85 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.03)]">
        <div class="h-16 sm:h-20 sc-landing-canvas flex items-center justify-between gap-3 sm:gap-4">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 min-w-0 shrink-0">
                <span class="w-10 h-10 rounded-xl bg-[#2563eb] text-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">favorite</span>
                </span>
                <span class="flex flex-col min-w-0">
                    <span class="font-display text-[18px] leading-6 font-bold tracking-tight">SantéConnect</span>
                    <span class="text-[11px] text-[#434655] leading-none">Espace médical</span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-1" aria-label="Navigation principale">
                <a href="{{ route('landing') }}" class="sc-nav-link-active" aria-current="page">Accueil</a>
                <a href="{{ route('landing') }}#specialites" class="sc-nav-link">Spécialités</a>
                <a href="{{ $isPatient ? route('patient.doctors.index') : route('login') }}" class="sc-nav-link">Annuaire</a>
                @auth
                    <a href="{{ auth()->user()->dashboardPath() }}" class="sc-nav-link">Mon espace</a>
                @endauth
            </nav>

            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-1 bg-[#ffdad6] text-[#93000a] px-3 py-1.5 rounded-full">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">emergency</span>
                    <span class="text-[12px] uppercase tracking-wider font-semibold">Urgence : 15 · 112</span>
                </div>
                @auth
                    <a href="{{ auth()->user()->dashboardPath() }}" class="hidden sm:inline text-sm font-semibold text-[#003f87] hover:underline">{{ auth()->user()->name }}</a>
                    <form action="{{ route('logout') }}" method="POST" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="sc-btn-primary !min-h-10 !px-4 !py-2 text-sm">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline text-sm font-semibold text-[#003f87] hover:underline">Connexion</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex sc-btn-primary !min-h-10 !px-4 !py-2 text-sm">Inscription</a>
                @endauth
                <button type="button"
                        class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-[#dbe1ff] text-[#434655]"
                        @click="landingNav = !landingNav"
                        :aria-expanded="landingNav.toString()"
                        aria-controls="landing-mobile-nav"
                        :aria-label="landingNav ? 'Fermer le menu' : 'Ouvrir le menu'">
                    <span class="material-symbols-outlined" aria-hidden="true" x-text="landingNav ? 'close' : 'menu'">menu</span>
                </button>
            </div>
        </div>
        <div x-show="landingNav" x-cloak class="lg:hidden fixed inset-0 top-16 sm:top-20 bg-slate-900/40 z-40" @click="landingNav = false" aria-hidden="true"></div>
        <div id="landing-mobile-nav"
             class="lg:hidden relative z-50 border-t border-[#dbe1ff] px-4 py-3 space-y-1 bg-[#faf8ff]"
             x-show="landingNav"
             x-cloak
             x-transition>
            <a href="{{ route('landing') }}" class="block sc-nav-link-active" @click="landingNav = false">Accueil</a>
            <a href="{{ route('landing') }}#specialites" class="block sc-nav-link" @click="landingNav = false">Spécialités</a>
            <a href="{{ $isPatient ? route('patient.doctors.index') : route('login') }}" class="block sc-nav-link" @click="landingNav = false">Annuaire</a>
            @auth
                <a href="{{ auth()->user()->dashboardPath() }}" class="block sc-nav-link" @click="landingNav = false">Mon espace</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sc-nav-link w-full text-left">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block sc-nav-link">Connexion</a>
                <a href="{{ route('register') }}" class="block sc-nav-link">Inscription</a>
            @endauth
        </div>
    </header>

    <div class="relative w-full overflow-hidden pt-16 sm:pt-20">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[980px] h-[480px] bg-gradient-to-b from-[#dbe1ff]/40 via-[#86f2e4]/20 to-transparent blur-3xl pointer-events-none -z-10 rounded-full"></div>

        <section class="sc-landing-canvas pt-8 sm:pt-12 lg:pt-16 pb-8 sm:pb-10">
            <div class="flex flex-col items-center text-center max-w-4xl mx-auto gap-5 sm:gap-6">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#e2e7ff] text-[#004ac6] shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-[#007d55] animate-pulse"></span>
                    <span class="text-[12px] font-semibold tracking-wide">Annuaire de médecins validés</span>
                </div>
                <h1 class="sc-landing-hero-title">
                    Prendre rendez-vous simplement :<br class="hidden sm:block">
                    <span class="bg-gradient-to-r from-[#004ac6] via-[#2563eb] to-[#006a61] bg-clip-text text-transparent">trouvez un médecin</span> et un créneau réel
                </h1>
                <p class="text-base lg:text-lg leading-7 text-[#434655] max-w-2xl">
                    Consultez les profils validés, demandez un créneau disponible et suivez le statut de votre demande. La téléconsultation n’est pas gérée.
                </p>
            </div>

            <div class="sc-landing-search-card">
                    <form method="GET" action="{{ $searchAction }}" class="sc-landing-search-form">
                        <div class="md:col-span-4 sc-landing-field">
                            <span class="material-symbols-outlined text-[#004ac6] text-[22px] shrink-0" aria-hidden="true">stethoscope</span>
                            <div class="flex flex-col text-left w-full min-w-0">
                                <label class="text-[11px] text-[#434655] font-medium" for="search-specialty">Spécialité ou médecin</label>
                                <input id="search-specialty" name="search" type="search" value="{{ request('search') }}"
                                       placeholder="ex. Cardiologie, nom du médecin"
                                       class="placeholder:text-[#737686] truncate">
                            </div>
                        </div>
                        <div class="md:col-span-3 sc-landing-field">
                            <span class="material-symbols-outlined text-[#006a61] text-[22px] shrink-0" aria-hidden="true">location_on</span>
                            <div class="flex flex-col text-left w-full min-w-0">
                                <label class="text-[11px] text-[#434655] font-medium" for="search-location">Ville</label>
                                <input id="search-location" type="text" placeholder="Filtre ville non disponible"
                                       class="placeholder:text-[#737686] truncate"
                                       readonly
                                       @focus="Alpine.$data(document.body).showToast('Le filtre par ville n’est pas enregistré.')">
                            </div>
                        </div>
                        <div class="md:col-span-3 sc-landing-field">
                            <span class="material-symbols-outlined text-[#737686] text-[22px] shrink-0" aria-hidden="true">calendar_month</span>
                            <div class="flex flex-col text-left w-full min-w-0">
                                <label class="text-[11px] text-[#434655] font-medium" for="search-date">Date souhaitée</label>
                                <x-ui.select name="when" id="search-date" label="Date souhaitée" :plain="true">
                                    <option value="">Toutes les dates</option>
                                    <option value="today">Aujourd’hui</option>
                                    <option value="today_tomorrow">Aujourd’hui et demain</option>
                                    <option value="7d">7 prochains jours</option>
                                </x-ui.select>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="sc-landing-search-btn">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span>
                                Rechercher
                            </button>
                        </div>
                    </form>
                    <div class="flex flex-wrap items-center gap-2 pt-3 px-1 text-[12px] text-[#434655]">
                        <span class="font-semibold text-[#737686]">Spécialités :</span>
                        @foreach($specialties->take(4) as $chip)
                            <a href="{{ $isPatient ? route('patient.doctors.index', ['speciality' => $chip->id]) : route('login') }}"
                               class="px-3 py-1 rounded-full bg-[#eaedff] hover:bg-[#e2e7ff] transition-colors">{{ $chip->name }}</a>
                        @endforeach
                        @if($isPatient)
                            <a href="{{ route('patient.doctors.index', ['when' => 'today']) }}" class="px-3 py-1 rounded-full bg-[#eaedff] hover:bg-[#e2e7ff]">Aujourd’hui</a>
                        @endif
                    </div>
                </div>

            <div class="mt-10 rounded-2xl bg-white shadow-sm p-5 lg:p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
                    <div class="flex flex-col items-center text-center p-2">
                        <span class="sc-landing-kpi text-[#004ac6]">{{ $doctorCount }}</span>
                        <span class="text-[12px] text-[#434655] mt-1">Médecins validés</span>
                    </div>
                    <div class="flex flex-col items-center text-center p-2">
                        <span class="sc-landing-kpi text-[#006a61]">{{ $specialtyCount }}</span>
                        <span class="text-[12px] text-[#434655] mt-1">Spécialités enregistrées</span>
                    </div>
                    <div class="flex flex-col items-center text-center p-2">
                        <span class="sc-landing-kpi text-[#007d55]">{{ $appointmentCount }}</span>
                        <span class="text-[12px] text-[#434655] mt-1">Rendez-vous enregistrés</span>
                    </div>
                    <div class="flex flex-col items-center text-center p-2">
                        <span class="sc-landing-kpi text-[#004ac6]">15 / 112</span>
                        <span class="text-[12px] text-[#434655] mt-1">Urgences</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="specialites" class="sc-landing-canvas py-10 lg:py-16">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-3">
                <div>
                    <span class="text-[12px] text-[#004ac6] tracking-wider uppercase font-semibold">Soins spécialisés</span>
                    <h2 class="sc-landing-section-title mt-1">Explorer les spécialités</h2>
                </div>
                <a href="{{ $isPatient ? route('patient.doctors.index') : route('login') }}"
                   class="inline-flex items-center gap-1 text-[14px] text-[#004ac6] font-semibold">
                    Voir les {{ $specialtyCount }} spécialité{{ $specialtyCount > 1 ? 's' : '' }}
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($specialties as $index => $specialty)
                    @php $tone = $iconTone[$index % count($iconTone)][0]; @endphp
                    <a href="{{ $isPatient ? route('patient.doctors.index', ['speciality' => $specialty->id]) : route('login') }}"
                       class="group p-6 rounded-2xl bg-white hover:bg-[#e2e7ff] transition-all shadow-sm hover:shadow-md flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl {{ $tone }} flex items-center justify-center">
                                <span class="material-symbols-outlined text-[30px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">{{ $iconFor($specialty->name) }}</span>
                            </div>
                            <div>
                                <h3 class="font-display text-[16px] leading-6 text-[#131b2e] group-hover:text-[#004ac6]">{{ $specialty->name }}</h3>
                                <p class="text-[13px] text-[#434655] mt-0.5">{{ $specialty->doctors_count }} médecin{{ $specialty->doctors_count > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-[#737686] group-hover:text-[#004ac6]" aria-hidden="true">chevron_right</span>
                    </a>
                @empty
                    <div class="col-span-full bg-white rounded-2xl p-8 text-center text-sm text-[#434655]">
                        Aucune spécialité n’est encore enregistrée.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="sc-landing-canvas py-10 sm:py-12 lg:py-16">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-3">
                <div>
                    <span class="text-[12px] text-[#006242] tracking-wider uppercase font-semibold">Praticiens validés</span>
                    <h2 class="sc-landing-section-title mt-1">Médecins validés</h2>
                    <p class="text-[15px] text-[#434655]">Profils réellement présents dans l’application. Consultation en cabinet uniquement.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($doctors as $doctor)
                    @php
                        $slots = $doctor->availabilities->take(3);
                        $next = $slots->first();
                        $photo = $doctor->user->profile_image_url ?? ($doctor->photo ? asset('storage/'.$doctor->photo) : null);
                        $profileUrl = $isPatient ? route('patient.doctors.show', $doctor) : route('login');
                        $bookUrl = ($isPatient && $next)
                            ? route('patient.appointments.create', ['availability_id' => $next->id])
                            : $profileUrl;
                    @endphp
                    <article class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-start gap-4">
                                <div class="relative shrink-0">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden ring-2 ring-[#dbe1ff] bg-[#e2e7ff] flex items-center justify-center">
                                        @if($photo)
                                            <img src="{{ $photo }}" alt="Photo de Dr. {{ $doctor->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-[#004ac6] font-display font-bold" aria-hidden="true">{{ strtoupper(substr($doctor->user->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    @if($next && $next->date->isToday())
                                        <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-[#007d55] rounded-full ring-2 ring-white" title="Créneau aujourd’hui"></span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-[#dbe1ff] text-[#00174b] font-medium">{{ $doctor->speciality->name }}</span>
                                    <h3 class="font-display text-[16px] leading-6 font-bold mt-1 truncate">Dr. {{ $doctor->user->name }}</h3>
                                    <p class="text-[13px] text-[#434655] truncate">{{ $doctor->address ?: 'Adresse non renseignée' }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 my-4 p-2 rounded-xl bg-[#f2f3ff]">
                                <div>
                                    <span class="text-[11px] text-[#434655] block">Créneaux libres</span>
                                    <span class="text-[14px] font-bold">{{ $doctor->available_slots_count }}</span>
                                </div>
                                <div>
                                    <span class="text-[11px] text-[#434655] block">Prochain créneau</span>
                                    <span class="text-[14px] font-bold text-[#006242]">
                                        {{ $next ? $next->date->format('d/m').' · '.substr($next->start_time, 0, 5) : 'Aucun' }}
                                    </span>
                                </div>
                            </div>
                            @if($slots->isNotEmpty())
                                <div class="space-y-1.5">
                                    <span class="text-[11px] text-[#737686] block">Créneaux proches</span>
                                    <div class="flex items-center gap-1 flex-wrap">
                                        @foreach($slots as $slot)
                                            <a href="{{ $isPatient ? route('patient.appointments.create', ['availability_id' => $slot->id]) : route('login') }}"
                                               class="px-2.5 py-1 rounded-lg text-[12px] font-semibold bg-[#eaedff] hover:bg-[#2563eb] hover:text-white text-[#131b2e]">
                                                {{ substr($slot->start_time, 0, 5) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-[13px] text-[#434655]">Aucun créneau libre pour le moment.</p>
                            @endif
                        </div>
                        <div class="pt-4 mt-4 flex items-center gap-2">
                            <a href="{{ $bookUrl }}" class="flex-1 h-11 rounded-xl bg-[#2563eb] hover:bg-[#004ac6] text-white text-[14px] font-semibold shadow-sm inline-flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">calendar_today</span>
                                {{ $isPatient ? 'Prendre rendez-vous' : 'Se connecter' }}
                            </a>
                            <button type="button" class="w-11 h-11 rounded-xl bg-[#eaedff] hover:bg-[#e2e7ff] flex items-center justify-center"
                                    title="Téléconsultation non gérée"
                                    @click="Alpine.$data(document.body).showToast('La téléconsultation n’est pas gérée.')">
                                <span class="material-symbols-outlined text-[20px] text-[#004ac6]" aria-hidden="true">videocam</span>
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-white rounded-2xl p-10 text-center">
                        <p class="font-semibold">Aucun médecin validé pour le moment</p>
                        <p class="text-sm text-[#434655] mt-1">Un administrateur doit valider les comptes médecins avant qu’ils n’apparaissent ici.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="sc-landing-canvas py-10 sm:py-12 lg:py-16">
            <div class="rounded-3xl bg-[#f2f3ff] p-6 lg:p-10">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <span class="text-[12px] text-[#004ac6] tracking-wider uppercase font-semibold">Parcours</span>
                    <h2 class="sc-landing-section-title mt-1">Comment fonctionne SantéConnect</h2>
                    <p class="text-[15px] text-[#434655] mt-2">Trois étapes réelles, sans confirmation instantanée ni téléconsultation.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-white shadow-sm relative">
                        <div class="absolute -top-4 w-8 h-8 rounded-full bg-[#004ac6] text-white text-[14px] font-bold flex items-center justify-center">1</div>
                        <div class="w-16 h-16 rounded-2xl bg-[#dae2fd] text-[#004ac6] flex items-center justify-center my-4">
                            <span class="material-symbols-outlined text-[32px]" aria-hidden="true">manage_search</span>
                        </div>
                        <h3 class="font-display text-[16px] leading-6 font-semibold">1. Trouvez un spécialiste</h3>
                        <p class="text-[15px] text-[#434655] mt-2">Filtrez l’annuaire par nom ou spécialité parmi les profils validés.</p>
                    </div>
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-white shadow-sm relative">
                        <div class="absolute -top-4 w-8 h-8 rounded-full bg-[#2563eb] text-white text-[14px] font-bold flex items-center justify-center">2</div>
                        <div class="w-16 h-16 rounded-2xl bg-[#89f5e7] text-[#005049] flex items-center justify-center my-4">
                            <span class="material-symbols-outlined text-[32px]" aria-hidden="true">event_available</span>
                        </div>
                        <h3 class="font-display text-[16px] leading-6 font-semibold">2. Choisissez un créneau</h3>
                        <p class="text-[15px] text-[#434655] mt-2">Seuls les horaires encore libres sont proposés. La demande reste en attente d’acceptation.</p>
                    </div>
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-white shadow-sm relative">
                        <div class="absolute -top-4 w-8 h-8 rounded-full bg-[#007d55] text-white text-[14px] font-bold flex items-center justify-center">3</div>
                        <div class="w-16 h-16 rounded-2xl bg-[#6ffbbe] text-[#005236] flex items-center justify-center my-4">
                            <span class="material-symbols-outlined text-[32px]" aria-hidden="true">local_hospital</span>
                        </div>
                        <h3 class="font-display text-[16px] leading-6 font-semibold">3. Consultez au cabinet</h3>
                        <p class="text-[15px] text-[#434655] mt-2">Le rendez-vous se fait en présentiel. Aucune ordonnance numérique n’est générée ici.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="sc-landing-canvas py-10 sm:py-12 lg:py-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <div class="lg:col-span-6 space-y-4">
                    <span class="text-[12px] text-[#004ac6] tracking-wider uppercase font-semibold">Avis</span>
                    <h2 class="sc-landing-section-title">Ce que l’application enregistre</h2>
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <p class="text-[18px] leading-7 text-[#131b2e]">Aucun avis patient n’est encore enregistré.</p>
                        <p class="text-[13px] text-[#434655] mt-2">SantéConnect n’affiche pas de témoignage, de note ou de délai moyen fictif.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-[#f2f3ff] p-4 rounded-xl">
                            <span class="font-display text-[20px] leading-7 text-[#004ac6] font-bold">{{ $doctorCount }}</span>
                            <p class="text-[13px] text-[#434655] mt-1">Médecins validés actuellement visibles.</p>
                        </div>
                        <div class="bg-[#f2f3ff] p-4 rounded-xl">
                            <span class="font-display text-[20px] leading-7 text-[#006a61] font-bold">En attente</span>
                            <p class="text-[13px] text-[#434655] mt-1">Chaque demande attend l’acceptation du médecin.</p>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-6 bg-gradient-to-br from-[#004ac6] via-[#2563eb] to-[#006a61] text-white rounded-3xl p-6 lg:p-8 shadow-xl relative overflow-hidden">
                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/10">
                            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">smartphone</span>
                            <span class="text-[11px] font-semibold tracking-wide">Application mobile</span>
                        </div>
                        <h3 class="font-display text-2xl lg:text-3xl leading-9 font-bold">Pas d’application iOS ou Android publiée</h3>
                        <p class="text-[15px] text-white/90">Les boutons ci-dessous n’ouvrent aucun store. Utilisez le site pour rechercher un médecin. En urgence, appelez le 15 ou le 112.</p>
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <button type="button" class="px-4 py-2.5 rounded-xl bg-white text-[#004ac6] text-[14px] font-semibold inline-flex items-center gap-2"
                                    @click="Alpine.$data(document.body).showToast('Aucune application mobile n’est publiée.')">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">file_download</span>
                                App Store
                            </button>
                            <button type="button" class="px-4 py-2.5 rounded-xl bg-white text-[#004ac6] text-[14px] font-semibold inline-flex items-center gap-2"
                                    @click="Alpine.$data(document.body).showToast('Aucune application mobile n’est publiée.')">
                                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">android</span>
                                Google Play
                            </button>
                        </div>
                    </div>
                    <div class="relative z-10 mt-6 pt-4 border-t border-white/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#ffdad6] text-[24px]" aria-hidden="true">e911_emergency</span>
                            <div>
                                <p class="text-[11px] text-white/80 uppercase font-semibold">Urgence vitale ?</p>
                                <p class="text-[14px] font-bold">Composez le 15 ou le 112</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="w-full bg-[#f2f3ff] shadow-[0_-1px_8px_rgba(15,23,42,0.02)] mt-8">
        <div class="sc-landing-canvas py-10 lg:py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-2">
                    <p class="font-display text-[16px] leading-6 text-[#004ac6] font-bold">SantéConnect</p>
                    <p class="text-[13px] text-[#434655] max-w-xs">Prise de rendez-vous médicaux en ligne sur des créneaux réels.</p>
                </div>
                <div>
                    <h2 class="font-display text-[16px] leading-6 mb-4 font-semibold">Annuaire</h2>
                    <ul class="space-y-1 text-[13px] text-[#434655]">
                        <li><a class="hover:text-[#004ac6]" href="{{ $isPatient ? route('patient.doctors.index') : route('login') }}">Trouver un médecin</a></li>
                        <li><a class="hover:text-[#004ac6]" href="#specialites">Spécialités</a></li>
                        <li><a class="hover:text-[#004ac6]" href="{{ route('register') }}">Créer un compte</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="font-display text-[16px] leading-6 mb-4 font-semibold">Espaces</h2>
                    <ul class="space-y-1 text-[13px] text-[#434655]">
                        <li><a class="hover:text-[#004ac6]" href="{{ route('login') }}">Connexion</a></li>
                        <li><a class="hover:text-[#004ac6]" href="{{ route('login') }}">Espace médecin</a></li>
                        <li><a class="hover:text-[#004ac6]" href="{{ route('login') }}">Espace admin</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="font-display text-[16px] leading-6 mb-4 font-semibold">Urgence</h2>
                    <div class="p-4 bg-white rounded-xl shadow-sm space-y-1">
                        <p class="text-[12px] text-[#ba1a1a] font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">medical_services</span>
                            SAMU : 15
                        </p>
                        <p class="text-[13px] text-[#434655]">En cas d’urgence vitale, appelez le 15 ou le 112. SantéConnect ne remplace pas un service d’urgence.</p>
                    </div>
                </div>
            </div>
            <p class="pt-6 mt-6 text-[13px] text-[#434655]">© {{ now()->year }} SantéConnect. Tous droits réservés.</p>
        </div>
    </footer>
</div>
@endsection
