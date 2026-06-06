@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Mes disponibilités</h2>
        <a href="{{ route('doctor.availabilities.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Ajouter disponibilité
        </a>
    </div>

    <div class="space-y-4">
        @forelse($availabilities as $availability)
            <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
                <div>
                    <p class="font-semibold text-gray-900">{{ $availability->date->format('d/m/Y') }}</p>
                    <p class="text-sm text-gray-600">{{ $availability->start_time }} - {{ $availability->end_time }}</p>
                    <p class="text-sm mt-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $availability->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $availability->is_available ? 'Disponible' : 'Occupée' }}
                        </span>
                    </p>
                </div>
                <form method="POST" action="{{ route('doctor.availabilities.destroy', $availability) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                </form>
            </div>
        @empty
            <p class="text-gray-600">Aucune disponibilité trouvée.</p>
        @endforelse
    </div>

    {{ $availabilities->links() }}
</div>
@endsection
