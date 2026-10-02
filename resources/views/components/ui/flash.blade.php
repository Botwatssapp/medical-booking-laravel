@php
    $hasSuccess = session()->has('success');
    $hasError = session()->has('error');
    $hasWarning = session()->has('warning');
    $hasStatus = session()->has('status');
    $hasValidation = $errors->any();
@endphp

@if($hasSuccess || $hasError || $hasWarning || $hasStatus || $hasValidation)
    <div class="{{ $wrapperClass ?? 'sc-page-wide mt-4' }}" data-sc-flash>
        @if($hasSuccess)
            <div x-data="{ show: true }" x-show="show" x-cloak role="status" class="mb-3 last:mb-0">
                <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
                    <span class="material-symbols-outlined text-green-600 shrink-0" aria-hidden="true">check_circle</span>
                    <p class="flex-1 min-w-0">{{ session('success') }}</p>
                    <button type="button" class="shrink-0 p-1 rounded-lg hover:bg-green-100" @click="show = false" aria-label="Fermer le message de succès">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if($hasStatus)
            <div x-data="{ show: true }" x-show="show" x-cloak role="status" class="mb-3 last:mb-0">
                <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
                    <span class="material-symbols-outlined text-green-600 shrink-0" aria-hidden="true">info</span>
                    <p class="flex-1 min-w-0">{{ session('status') }}</p>
                    <button type="button" class="shrink-0 p-1 rounded-lg hover:bg-green-100" @click="show = false" aria-label="Fermer le message">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if($hasWarning)
            <div x-data="{ show: true }" x-show="show" x-cloak role="status" class="mb-3 last:mb-0">
                <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm font-medium">
                    <span class="material-symbols-outlined text-amber-600 shrink-0" aria-hidden="true">warning</span>
                    <p class="flex-1 min-w-0">{{ session('warning') }}</p>
                    <button type="button" class="shrink-0 p-1 rounded-lg hover:bg-amber-100" @click="show = false" aria-label="Fermer l’avertissement">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if($hasError)
            <div x-data="{ show: true }" x-show="show" x-cloak role="alert" class="mb-3 last:mb-0">
                <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">
                    <span class="material-symbols-outlined text-red-600 shrink-0" aria-hidden="true">error</span>
                    <p class="flex-1 min-w-0">{{ session('error') }}</p>
                    <button type="button" class="shrink-0 p-1 rounded-lg hover:bg-red-100" @click="show = false" aria-label="Fermer le message d’erreur">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if($hasValidation)
            <div x-data="{ show: true }" x-show="show" x-cloak role="alert" class="mb-3 last:mb-0">
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-red-600 shrink-0" aria-hidden="true">error</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-red-800 mb-1">Veuillez corriger les champs indiqués.</p>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="shrink-0 p-1 rounded-lg hover:bg-red-100" @click="show = false" aria-label="Fermer les erreurs de formulaire">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">close</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
