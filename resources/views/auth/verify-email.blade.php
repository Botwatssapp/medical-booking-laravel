<x-guest-layout>
    <h1 class="text-xl font-bold text-[#0d1c2f] mb-2">Vérifiez votre e-mail</h1>
    <p class="mb-4 text-sm text-[#526069]">
        Merci pour votre inscription. Cliquez sur le lien envoyé à votre adresse e-mail pour activer votre compte. Si vous n’avez rien reçu, nous pouvons renvoyer le message.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-800 bg-green-50 border border-green-200 rounded-xl px-4 py-3" role="status">
            Un nouveau lien de vérification a été envoyé.
        </div>
    @endif

    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                Renvoyer l’e-mail
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-[#526069] hover:text-[#003f87] underline rounded-md">
                Déconnexion
            </button>
        </form>
    </div>
</x-guest-layout>
