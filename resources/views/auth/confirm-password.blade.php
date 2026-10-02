<x-guest-layout>
    <h1 class="text-xl font-bold text-[#0d1c2f] mb-2">Confirmer votre mot de passe</h1>
    <p class="mb-4 text-sm text-[#526069]">
        Cette action est sensible. Confirmez votre mot de passe pour continuer.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-primary-button>
                Confirmer
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
