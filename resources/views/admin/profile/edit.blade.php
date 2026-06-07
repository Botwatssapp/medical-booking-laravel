@extends('layouts.admin')
@section('page-title', 'Mon profil')
@section('page-subtitle', 'Gérer les informations de votre compte administrateur')

@section('admin-content')
<div class="max-w-2xl space-y-6">

    {{-- Formulaire suppression photo — hors du formulaire principal --}}
    @if($user->profile_image)
        <form id="remove-image-form" method="POST" action="{{ route('admin.profile.removeImage') }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif

    {{-- ── Photo + infos principales ── --}}
    <form method="POST" action="{{ route('admin.profile.update') }}"
          enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-[#e0e7ff] p-8 space-y-6">
        @csrf @method('PATCH')

        {{-- Photo de profil --}}
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

        <hr class="border-[#e0e7ff]">

        {{-- Nom & email --}}
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                    Nom complet <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">
                    Adresse email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                              focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <hr class="border-[#e0e7ff]">

        {{-- Changement de mot de passe --}}
        <div>
            <p class="text-sm font-bold text-[#0d1c2f] mb-3">Changer le mot de passe</p>
            <p class="text-xs text-[#526069] mb-4">Laissez vide si vous ne souhaitez pas changer le mot de passe.</p>
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password"
                           class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                                  focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    @error('current_password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password"
                               class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                                      focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                        @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-[#0d1c2f] mb-1.5">Confirmer</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full border border-[#c2c6d4] rounded-xl px-4 py-3 text-[#0d1c2f]
                                      focus:outline-none focus:ring-2 focus:ring-[#003f87]/30 focus:border-[#003f87] transition-colors">
                    </div>
                </div>
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
