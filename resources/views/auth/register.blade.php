<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SantéConnect — Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #050e1d; }
        .bg-dots {
            background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        input:-webkit-autofill,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px #0f1e35 inset;
            -webkit-text-fill-color: #fff;
        }
        .role-card { transition: all 0.2s; cursor: pointer; }
        .role-card:has(input:checked) {
            border-color: #3b82f6;
            background: rgba(59,130,246,0.12);
        }
        .role-card:has(input:checked) .role-icon {
            background: rgba(59,130,246,0.25);
            color: #93c5fd;
        }
        .role-card:has(input:checked) .role-title { color: #fff; }
        .role-check {
            opacity: 0; transition: opacity 0.15s;
        }
        .role-card:has(input:checked) .role-check { opacity: 1; }
    </style>
</head>
<body class="min-h-screen bg-dots flex items-center justify-center p-4 relative overflow-hidden">

    {{-- Orbs --}}
    <div class="fixed top-[-10%] right-[-10%]  w-[550px] h-[550px] bg-[#003f87] rounded-full opacity-[0.16] blur-[130px] pointer-events-none"></div>
    <div class="fixed bottom-[-20%] left-[-8%] w-[500px] h-[500px] bg-[#0a4d35] rounded-full opacity-[0.13] blur-[110px] pointer-events-none"></div>

    <div class="w-full max-w-[1040px] grid grid-cols-1 lg:grid-cols-2 rounded-3xl overflow-hidden shadow-[0_30px_80px_rgba(0,0,0,0.5)] border border-white/8 my-6">

        {{-- ══ Panneau gauche — Formulaire ══ --}}
        <div class="bg-[#080f1c] flex items-center justify-center p-8 md:p-12 order-2 lg:order-1">
            <div class="w-full max-w-[360px]">

                {{-- Logo mobile --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 mb-8 lg:hidden">
                    <div class="w-8 h-8 bg-[#003f87] rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-base">favorite</span>
                    </div>
                    <span class="text-white font-bold">SantéConnect</span>
                </a>

                <div class="mb-7">
                    <p class="text-xs font-bold uppercase tracking-widest text-[#3b82f6] mb-3">Inscription</p>
                    <h2 class="text-2xl font-bold text-white mb-1.5">Créez votre compte</h2>
                    <p class="text-white/40 text-sm">Rejoignez SantéConnect en quelques secondes</p>
                </div>

                {{-- Erreurs --}}
                @if($errors->any())
                    <div class="border border-red-500/30 bg-red-500/10 rounded-xl px-4 py-3 mb-6 flex items-start gap-3">
                        <span class="material-symbols-outlined text-red-400 text-base shrink-0 mt-0.5">error</span>
                        <div>
                            @foreach($errors->all() as $error)
                                <p class="text-sm text-red-300">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- ── Sélecteur de rôle ── --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-white/40 mb-3">
                            Je suis
                        </label>
                        <div class="grid grid-cols-2 gap-3">

                            {{-- Patient --}}
                            <label class="role-card relative border border-white/10 rounded-xl p-4 bg-white/4 hover:border-white/20">
                                <input type="radio" name="role" value="patient" class="sr-only"
                                       {{ old('role', 'patient') === 'patient' ? 'checked' : '' }}>
                                <div class="flex flex-col items-center gap-2.5 text-center">
                                    <div class="role-icon w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white/50 transition-all">
                                        <span class="material-symbols-outlined text-xl">person</span>
                                    </div>
                                    <div>
                                        <p class="role-title text-sm font-semibold text-white/60 transition-colors">Patient</p>
                                        <p class="text-[11px] text-white/30 mt-0.5">Prendre RDV</p>
                                    </div>
                                </div>
                                <span class="role-check absolute top-2.5 right-2.5 w-4 h-4 bg-[#3b82f6] rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-[10px]">check</span>
                                </span>
                            </label>

                            {{-- Médecin --}}
                            <label class="role-card relative border border-white/10 rounded-xl p-4 bg-white/4 hover:border-white/20">
                                <input type="radio" name="role" value="doctor" class="sr-only"
                                       {{ old('role') === 'doctor' ? 'checked' : '' }}>
                                <div class="flex flex-col items-center gap-2.5 text-center">
                                    <div class="role-icon w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white/50 transition-all">
                                        <span class="material-symbols-outlined text-xl">stethoscope</span>
                                    </div>
                                    <div>
                                        <p class="role-title text-sm font-semibold text-white/60 transition-colors">Médecin</p>
                                        <p class="text-[11px] text-white/30 mt-0.5">Gérer les RDV</p>
                                    </div>
                                </div>
                                <span class="role-check absolute top-2.5 right-2.5 w-4 h-4 bg-[#3b82f6] rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-[10px]">check</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Nom --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-white/40 mb-2">
                            Nom complet
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[18px]">badge</span>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   placeholder="Jean Dupont" required
                                   class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/20
                                          focus:outline-none focus:border-[#3b82f6]/60 focus:ring-1 focus:ring-[#3b82f6]/30 transition-all">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-white/40 mb-2">
                            Adresse e-mail
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[18px]">mail</span>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   placeholder="jean.dupont@email.com" required
                                   class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/20
                                          focus:outline-none focus:border-[#3b82f6]/60 focus:ring-1 focus:ring-[#3b82f6]/30 transition-all">
                        </div>
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-white/40 mb-2">
                            Mot de passe
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[18px]">lock</span>
                            <input type="password" name="password" id="pw"
                                   placeholder="Minimum 8 caractères" required
                                   class="w-full pl-10 pr-11 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/20
                                          focus:outline-none focus:border-[#3b82f6]/60 focus:ring-1 focus:ring-[#3b82f6]/30 transition-all">
                            <button type="button" id="toggle-pw"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/60 transition-colors">
                                <span class="material-symbols-outlined text-[18px]" id="eye-icon">visibility</span>
                            </button>
                        </div>

                        {{-- Indicateur de force --}}
                        <div class="flex gap-1 mt-2" id="strength-bars">
                            <div class="h-1 flex-1 rounded-full bg-white/10" id="bar1"></div>
                            <div class="h-1 flex-1 rounded-full bg-white/10" id="bar2"></div>
                            <div class="h-1 flex-1 rounded-full bg-white/10" id="bar3"></div>
                            <div class="h-1 flex-1 rounded-full bg-white/10" id="bar4"></div>
                        </div>
                    </div>

                    {{-- Confirmer --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-white/40 mb-2">
                            Confirmer le mot de passe
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[18px]">lock_open</span>
                            <input type="password" name="password_confirmation" id="pw2"
                                   placeholder="Répétez le mot de passe" required
                                   class="w-full pl-10 pr-11 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/20
                                          focus:outline-none focus:border-[#3b82f6]/60 focus:ring-1 focus:ring-[#3b82f6]/30 transition-all">
                            <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-[18px] opacity-0 transition-opacity" id="match-icon"></span>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full py-3.5 bg-[#003f87] hover:bg-[#0052b3] text-white font-semibold rounded-xl
                                   transition-all shadow-lg shadow-[#003f87]/20 mt-1
                                   flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        Créer mon compte
                    </button>
                </form>

                <div class="flex items-center gap-4 my-6">
                    <div class="flex-1 h-px bg-white/8"></div>
                    <span class="text-xs text-white/25">DÉJÀ INSCRIT ?</span>
                    <div class="flex-1 h-px bg-white/8"></div>
                </div>

                <a href="{{ route('login') }}"
                   class="w-full flex items-center justify-center gap-2 py-3 border border-white/10 rounded-xl
                          text-white/55 hover:text-white hover:border-white/25 text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[16px]">login</span>
                    Se connecter
                </a>
            </div>
        </div>

        {{-- ══ Panneau droit — Branding ══ --}}
        <div class="order-1 lg:order-2 bg-gradient-to-br from-[#001535] via-[#002d63] to-[#003f87] p-10 flex flex-col justify-between relative overflow-hidden min-h-[220px] lg:min-h-0">
            <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-16 -left-10 w-56 h-56 bg-white/4 rounded-full"></div>

            <div class="relative z-10">
                <a href="{{ route('home') }}" class="hidden lg:flex items-center gap-3 mb-14 group">
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center group-hover:bg-white/25 transition-colors">
                        <span class="material-symbols-outlined text-white text-xl">favorite</span>
                    </div>
                    <span class="text-white font-bold text-xl tracking-tight">SantéConnect</span>
                </a>

                <h2 class="text-3xl font-bold text-white leading-tight mb-4">
                    Rejoignez<br>
                    <span class="text-[#7eb8ff]">des milliers</span><br>
                    de patients.
                </h2>
                <p class="text-white/55 text-sm leading-relaxed max-w-xs">
                    Une plateforme médicale moderne pour prendre soin de vous et de vos proches.
                </p>
            </div>

            {{-- Stats --}}
            <div class="relative z-10 grid grid-cols-2 gap-3 mt-8">
                @foreach([
                    ['value' => '500+',  'label' => 'Médecins',     'icon' => 'stethoscope'],
                    ['value' => '10k+',  'label' => 'Patients',     'icon' => 'group'],
                    ['value' => '50k+',  'label' => 'Rendez-vous',  'icon' => 'calendar_month'],
                    ['value' => '4.9★',  'label' => 'Satisfaction', 'icon' => 'star'],
                ] as $stat)
                    <div class="bg-white/8 border border-white/10 rounded-xl p-3.5 flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#7eb8ff] text-[18px]">{{ $stat['icon'] }}</span>
                        <div>
                            <p class="text-white font-bold text-lg leading-none">{{ $stat['value'] }}</p>
                            <p class="text-white/45 text-[11px] mt-0.5">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
    // Toggle password
    const btn  = document.getElementById('toggle-pw');
    const icon = document.getElementById('eye-icon');
    const pw   = document.getElementById('pw');
    const pw2  = document.getElementById('pw2');
    const matchIcon = document.getElementById('match-icon');

    btn.addEventListener('click', () => {
        const show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        icon.textContent = show ? 'visibility_off' : 'visibility';
    });

    // Password strength
    const bars = ['bar1','bar2','bar3','bar4'].map(id => document.getElementById(id));
    const colors = ['bg-red-500','bg-orange-500','bg-yellow-400','bg-green-500'];

    pw.addEventListener('input', () => {
        const val = pw.value;
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        bars.forEach((b, i) => {
            b.className = 'h-1 flex-1 rounded-full transition-all ' + (i < score ? colors[score - 1] : 'bg-white/10');
        });
        checkMatch();
    });

    // Match check
    pw2.addEventListener('input', checkMatch);
    function checkMatch() {
        if (!pw2.value) { matchIcon.style.opacity = '0'; return; }
        const match = pw.value === pw2.value;
        matchIcon.textContent = match ? 'check_circle' : 'cancel';
        matchIcon.className = 'material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-[18px] transition-opacity ' +
                              (match ? 'text-green-400' : 'text-red-400');
        matchIcon.style.opacity = '1';
    }
    </script>
</body>
</html>
