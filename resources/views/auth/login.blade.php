<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SantéConnect — Connexion</title>
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
    </style>
</head>
<body class="min-h-screen bg-dots flex items-center justify-center py-6 px-4 relative overflow-x-hidden">

    {{-- Orbs décoratifs --}}
    <div class="fixed top-[-15%] left-[-8%]  w-[600px] h-[600px] bg-[#003f87] rounded-full opacity-[0.18] blur-[130px] pointer-events-none"></div>
    <div class="fixed bottom-[-20%] right-[-5%] w-[480px] h-[480px] bg-[#0a4d35] rounded-full opacity-[0.14] blur-[110px] pointer-events-none"></div>

    <div class="w-full max-w-[980px] my-4 grid grid-cols-1 lg:grid-cols-5 rounded-3xl overflow-hidden shadow-[0_30px_80px_rgba(0,0,0,0.5)] border border-white/8">

        {{-- ══ Panneau gauche — Branding ══ --}}
        <div class="lg:col-span-2 bg-gradient-to-br from-[#003f87] via-[#002d63] to-[#001535] p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-16 -left-16 w-56 h-56 bg-white/6 rounded-full"></div>
            <div class="absolute -bottom-20 -right-12 w-72 h-72 bg-white/4 rounded-full"></div>

            <div class="relative z-10">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 mb-14 group">
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center group-hover:bg-white/25 transition-colors">
                        <span class="material-symbols-outlined text-white text-xl">favorite</span>
                    </div>
                    <span class="text-white font-bold text-xl tracking-tight">SantéConnect</span>
                </a>

                <h1 class="text-[2rem] font-bold text-white leading-tight mb-4">
                    Votre santé,<br>
                    <span class="text-[#7eb8ff]">simplifiée.</span>
                </h1>
                <p class="text-white/55 text-sm leading-relaxed">
                    Gérez vos rendez-vous, consultez vos praticiens et suivez votre santé en toute sécurité.
                </p>
            </div>

            {{-- Features --}}
            <div class="relative z-10 space-y-3 mt-10">
                @foreach([
                    ['icon' => 'event_available', 'text' => 'Prise de RDV en 1 clic'],
                    ['icon' => 'notifications',   'text' => 'Rappels automatiques'],
                    ['icon' => 'security',        'text' => 'Données 100% sécurisées'],
                ] as $f)
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 bg-white/12 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[#7eb8ff] text-[15px]">{{ $f['icon'] }}</span>
                        </div>
                        <span class="text-white/65 text-sm">{{ $f['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ══ Panneau droit — Formulaire ══ --}}
        <div class="lg:col-span-3 bg-[#080f1c] flex items-center justify-center p-8 lg:p-12 xl:p-14">
            <div class="w-full max-w-[340px]">

                {{-- Titre --}}
                <div class="mb-8">
                    <p class="text-xs font-bold uppercase tracking-widest text-[#3b82f6] mb-3">Connexion</p>
                    <h2 class="text-2xl font-bold text-white mb-1.5">Bon retour 👋</h2>
                    <p class="text-white/40 text-sm">Connectez-vous à votre espace personnel</p>
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

                {{-- Session status --}}
                @if(session('status'))
                    <div class="border border-green-500/30 bg-green-500/10 rounded-xl px-4 py-3 mb-6">
                        <p class="text-sm text-green-300">{{ session('status') }}</p>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-white/40 mb-2">
                            Adresse e-mail
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[18px]">mail</span>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   placeholder="nom@exemple.com" required autofocus
                                   class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/20
                                          focus:outline-none focus:border-[#3b82f6]/60 focus:ring-1 focus:ring-[#3b82f6]/30 transition-all">
                        </div>
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[11px] font-bold uppercase tracking-widest text-white/40">
                                Mot de passe
                            </label>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-xs text-[#7eb8ff] hover:text-white transition-colors">
                                    Oublié ?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[18px]">lock</span>
                            <input type="password" name="password" id="pw"
                                   placeholder="••••••••" required
                                   class="w-full pl-10 pr-11 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/20
                                          focus:outline-none focus:border-[#3b82f6]/60 focus:ring-1 focus:ring-[#3b82f6]/30 transition-all">
                            <button type="button" id="toggle-pw"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/25 hover:text-white/60 transition-colors">
                                <span class="material-symbols-outlined text-[18px]" id="eye-icon">visibility</span>
                            </button>
                        </div>
                    </div>

                    {{-- Se souvenir --}}
                    <label class="flex items-center gap-3 cursor-pointer group select-none">
                        <div class="relative w-5 h-5 shrink-0">
                            <input type="checkbox" name="remember" id="remember" class="peer sr-only">
                            <div class="w-5 h-5 rounded-md border border-white/15 bg-white/5
                                        peer-checked:bg-[#003f87] peer-checked:border-[#003f87]
                                        flex items-center justify-center transition-all">
                                <span class="material-symbols-outlined text-white text-[12px] opacity-0 peer-checked:opacity-100 transition-opacity" id="check-icon">check</span>
                            </div>
                        </div>
                        <span class="text-sm text-white/40 group-hover:text-white/60 transition-colors">Se souvenir de moi</span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full py-3.5 mt-1 bg-[#003f87] hover:bg-[#0052b3] text-white font-semibold rounded-xl
                                   transition-all shadow-lg shadow-[#003f87]/20
                                   flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">login</span>
                        Se connecter
                    </button>
                </form>

                {{-- Séparateur --}}
                <div class="flex items-center gap-4 my-7">
                    <div class="flex-1 h-px bg-white/8"></div>
                    <span class="text-xs text-white/25">NOUVEAU SUR SANTÉCONNECT ?</span>
                    <div class="flex-1 h-px bg-white/8"></div>
                </div>

                <a href="{{ route('register') }}"
                   class="w-full flex items-center justify-center gap-2 py-3 border border-white/10 rounded-xl
                          text-white/60 hover:text-white hover:border-white/25 text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    Créer un compte gratuit
                </a>
            </div>
        </div>
    </div>

    <script>
    // Toggle password visibility
    const btn  = document.getElementById('toggle-pw');
    const icon = document.getElementById('eye-icon');
    const inp  = document.getElementById('pw');
    if (btn) {
        btn.addEventListener('click', () => {
            const show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            icon.textContent = show ? 'visibility_off' : 'visibility';
        });
    }
    // Checkbox animation
    const cb = document.getElementById('remember');
    const checkIcon = document.getElementById('check-icon');
    if (cb) {
        cb.addEventListener('change', () => {
            checkIcon.style.opacity = cb.checked ? '1' : '0';
        });
    }
    </script>
</body>
</html>
