@extends('layouts.app')
@section('title', 'Mon profil')

@section('content')
<div class="max-w-3xl mx-auto px-8 py-10">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-[#0d1c2f]">Mon profil</h1>
        <p class="text-[#526069] mt-1">Modifiez vos informations personnelles.</p>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4 mb-6">
            <span class="material-symbols-outlined text-green-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire suppression photo — hors du formulaire principal --}}
    @if($user->profile_image)
        <form id="remove-image-form" method="POST" action="{{ route('patient.profile.removeImage') }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif

    <form method="POST" action="{{ route('patient.profile.update') }}"
          enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-[#c2c6d4]/30 shadow-sm p-8 space-y-6">
        @csrf
        @method('PATCH')

        {{-- ── Photo de profil ── --}}
        <div class="flex flex-col items-center gap-4">

            <div class="relative">
                <div class="w-28 h-28 rounded-full border-4 border-[#003f87]/20 overflow-hidden bg-[#eff4ff] flex items-center justify-center">
                    @if($user->profile_image_url)
                        <img id="avatar-preview" src="{{ $user->profile_image_url }}"
                             alt="Photo de profil" class="w-full h-full object-cover">
                    @else
                        <span id="avatar-initials" class="text-4xl font-bold text-[#003f87]">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <img id="avatar-preview" src="" alt="" class="w-full h-full object-cover hidden">
                    @endif
                </div>

                <label for="profile_image"
                       class="absolute bottom-0 right-0 w-9 h-9 bg-[#003f87] hover:bg-[#002d6b] text-white rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors"
                       title="Changer la photo">
                    <span class="material-symbols-outlined text-lg">photo_camera</span>
                </label>
            </div>

            <input type="file" id="profile_image" name="profile_image"
                   accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">

            <div class="text-center">
                <p class="text-xs text-[#526069]">JPEG, PNG, WebP — max 2 Mo</p>
                @if($user->profile_image)
                    <button type="button"
                            onclick="confirm('Supprimer la photo de profil ?') && document.getElementById('remove-image-form').submit()"
                            class="text-xs text-red-500 hover:text-red-700 hover:underline mt-1">
                        Supprimer la photo
                    </button>
                @endif
            </div>
        </div>

        <hr class="border-[#c2c6d4]/40">

        {{-- ── Identité ── --}}
        <p class="text-xs font-bold text-[#526069] uppercase tracking-wider">Identité</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                    Nom complet <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="email" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                    Adresse email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="gender" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Sexe</label>
                <select id="gender" name="gender"
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                               focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    <option value="">— Non renseigné —</option>
                    <option value="male"   {{ old('gender', $user->gender) === 'male'   ? 'selected' : '' }}>Homme</option>
                    <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Femme</option>
                    <option value="other"  {{ old('gender', $user->gender) === 'other'  ? 'selected' : '' }}>Autre</option>
                </select>
                @error('gender')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="birth_date" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Date de naissance</label>
                <input type="date" id="birth_date" name="birth_date"
                       value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                       max="{{ now()->subDay()->format('Y-m-d') }}"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('birth_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <hr class="border-[#c2c6d4]/40">

        {{-- ── Informations médicales ── --}}
        <p class="text-xs font-bold text-[#526069] uppercase tracking-wider">Informations médicales</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="blood_type" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Groupe sanguin</label>
                <select id="blood_type" name="blood_type"
                        class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                               focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    <option value="">— Non renseigné —</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                        <option value="{{ $bt }}" {{ old('blood_type', $user->blood_type) === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                    @endforeach
                </select>
                @error('blood_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="weight" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Poids (kg)</label>
                <input type="number" id="weight" name="weight" step="0.1" min="1" max="500"
                       value="{{ old('weight', $user->weight ? rtrim(rtrim($user->weight, '0'), '.') : '') }}"
                       placeholder="ex : 70"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('weight')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="height" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Taille (cm)</label>
                <input type="number" id="height" name="height" step="0.1" min="30" max="300"
                       value="{{ old('height', $user->height ? rtrim(rtrim($user->height, '0'), '.') : '') }}"
                       placeholder="ex : 175"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('height')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <hr class="border-[#c2c6d4]/40">

        {{-- ── Coordonnées ── --}}
        <p class="text-xs font-bold text-[#526069] uppercase tracking-wider">Coordonnées</p>
        <div class="space-y-4">
            <div>
                <label for="phone" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Téléphone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                       placeholder="ex : 0612345678"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Adresse</label>
                <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}"
                       placeholder="ex : 12 rue de la Santé, Paris"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="emergency_contact" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                    Contact d'urgence
                    <span class="text-xs font-normal text-[#526069]">(nom et téléphone)</span>
                </label>
                <input type="text" id="emergency_contact" name="emergency_contact"
                       value="{{ old('emergency_contact', $user->emergency_contact) }}"
                       placeholder="ex : Marie Dupont — 0698765432"
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('emergency_contact')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit"
            class="w-full py-3 bg-[#003f87] hover:opacity-90 text-white font-semibold rounded-xl transition-opacity">
            Enregistrer les modifications
        </button>
    </form>
</div>

<script>
document.getElementById('profile_image').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const preview  = document.getElementById('avatar-preview');
    const initials = document.getElementById('avatar-initials');
    const reader   = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (initials) initials.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
