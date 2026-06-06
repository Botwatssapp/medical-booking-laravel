@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Nouveau rendez-vous</h2>

    <form method="POST" action="{{ route('patient.appointments.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-900">Médecin *</label>
            <select name="doctor_id" required class="mt-1 w-full border rounded-lg px-4 py-2">
                <option value="">Sélectionner un médecin</option>
                @foreach(\App\Models\Doctor::with('user', 'speciality')->get() as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->user->name }} - {{ $doctor->speciality->name }}</option>
                @endforeach
            </select>
            @error('doctor_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Disponibilité *</label>
            <select name="availability_id" required class="mt-1 w-full border rounded-lg px-4 py-2">
                <option value="">Sélectionner une disponibilité</option>
            </select>
            @error('availability_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Date et heure du rendez-vous *</label>
            <input type="datetime-local" name="appointment_date" required class="mt-1 w-full border rounded-lg px-4 py-2">
            @error('appointment_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-900">Notes</label>
            <textarea name="notes" rows="4" class="mt-1 w-full border rounded-lg px-4 py-2">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Créer</button>
            <a href="{{ route('patient.appointments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded">Annuler</a>
        </div>
    </form>
</div>

<script>
document.querySelector('select[name="doctor_id"]').addEventListener('change', function() {
    fetch(`/api/doctor/${this.value}/availabilities`)
        .then(r => r.json())
        .then(data => {
            const select = document.querySelector('select[name="availability_id"]');
            select.innerHTML = '<option value="">Sélectionner une disponibilité</option>';
            data.forEach(a => {
                const option = document.createElement('option');
                option.value = a.id;
                option.textContent = `${a.date} ${a.start_time} - ${a.end_time}`;
                select.appendChild(option);
            });
        });
});
</script>
@endsection
