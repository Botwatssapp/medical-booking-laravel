@extends('layouts.admin')

@section('admin-content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-gray-900">Spécialités</h2>
        <a href="{{ route('admin.specialties.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Ajouter spécialité
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nom</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Description</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($specialties as $specialty)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $specialty->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ Str::limit($specialty->description, 50) }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.specialties.edit', $specialty) }}" class="text-blue-600 hover:text-blue-800">Éditer</a>
                            <form method="POST" action="{{ route('admin.specialties.destroy', $specialty) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">Aucune spécialité trouvée</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $specialties->links() }}
</div>
@endsection
