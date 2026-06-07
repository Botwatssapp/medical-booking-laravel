@extends('layouts.doctor')
@section('page-title', 'Mon profil')
@section('page-subtitle', 'Gérez votre photo et vos informations professionnelles')

@section('doctor-content')
<div class="max-w-2xl">

    {{-- Formulaire suppression photo — HORS du formulaire principal pour éviter l'imbrication --}}
    @if($user->profile_image)
        <form id="remove-image-form" method="POST" action="{{ route('doctor.profile.removeImage') }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif

    <form method="POST" action="{{ route('doctor.profile.update') }}"
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

        {{-- ── Informations professionnelles ── --}}
        @if($doctor)
            <div class="space-y-4">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Téléphone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $doctor->phone) }}"
                           class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                                  focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                </div>

                <div>
                    <label for="address" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Adresse du cabinet</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $doctor->address) }}"
                           class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                                  focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                </div>

                <div>
                    <label for="bio" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Biographie</label>
                    <textarea id="bio" name="bio" rows="4"
                              class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                                     focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">{{ old('bio', $doctor->bio) }}</textarea>
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
                <span class="material-symbols-outlined align-middle text-base mr-1">info</span>
                Votre profil médical est en attente de validation par un administrateur.
                Vous pouvez déjà mettre à jour votre photo.
            </div>
        @endif

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
