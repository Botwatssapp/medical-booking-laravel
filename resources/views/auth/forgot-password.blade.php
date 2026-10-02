<x-guest-layout>
    <h1 class="text-xl font-bold text-[#0d1c2f] mb-2">Mot de passe oublié</h1>
    <p class="mb-4 text-sm text-[#526069]">
        Indiquez votre adresse e-mail. Si un compte existe, vous recevrez un lien pour choisir un nouveau mot de passe.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#003f87] hover:underline">Retour à la connexion</a>
            <x-primary-button>
                Envoyer le lien
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
