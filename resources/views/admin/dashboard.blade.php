@extends('layouts.admin')

@section('admin-content')
<div class="space-y-6">
    <h2 class="text-3xl font-bold text-gray-900">Tableau de bord</h2>

    <!-- Cards Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-medium">Patients</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $totalPatients }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-medium">Médecins</h3>
            <p class="text-3xl font-bold text-green-600">{{ $totalDoctors }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-medium">Rendez-vous</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $totalAppointments }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-medium">Aujourd'hui</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $todayAppointments }}</p>
        </div>
    </div>

    <!-- Statistiques Rendez-vous -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Statut des rendez-vous</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Acceptés</span>
                    <span class="font-bold text-green-600">{{ $acceptedAppointments }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Annulés</span>
                    <span class="font-bold text-red-600">{{ $cancelledAppointments }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Non réalisés</span>
                    <span class="font-bold text-yellow-600">{{ $missedAppointments }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Rendez-vous par spécialité</h3>
            <div class="space-y-2">
                @foreach ($appointmentsBySpecialty as $specialty)
                    <div class="flex justify-between">
                        <span>{{ $specialty['name'] }}</span>
                        <span class="font-bold">{{ $specialty['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>
        <div class="flex gap-4">
            <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Ajouter utilisateur
            </a>
            <a href="{{ route('admin.doctors.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                Ajouter médecin
            </a>
            <a href="{{ route('admin.specialties.create') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                Ajouter spécialité
            </a>
        </div>
    </div>
</div>
@endsection
