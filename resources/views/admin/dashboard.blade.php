@extends('layouts.admin')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Vue d\'ensemble du système SantéConnect')

@section('admin-content')
<div class="space-y-6">

    {{-- ── Bandeau médecins en attente ── --}}
    @if($pendingDoctors->isNotEmpty())
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-xl">pending</span>
                </div>
                <div>
                    <p class="font-bold text-amber-900">{{ $pendingDoctors->count() }} compte{{ $pendingDoctors->count() > 1 ? 's' : '' }} médecin à valider</p>
                    <p class="text-sm text-amber-700">Ces médecins attendent la configuration de leur profil.</p>
                </div>
                <a href="{{ route('admin.users.index') }}"
                   class="ml-auto text-xs font-semibold text-amber-700 hover:text-amber-900 underline underline-offset-2">
                    Voir tous →
                </a>
            </div>
            <div class="space-y-2">
                @foreach($pendingDoctors->take(3) as $pending)
                    <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3 border border-amber-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-sm">
                                {{ strtoupper(substr($pending->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $pending->name }}</p>
                                <p class="text-xs text-gray-500">{{ $pending->email }} · inscrit le {{ $pending->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.doctors.create', ['user_id' => $pending->id]) }}"
                           class="flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Confirmer
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Stat cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-blue-600 text-2xl">person</span>
            </div>
            <div>
                <p class="text-[#526069] text-xs font-medium">Patients</p>
                <p class="text-3xl font-bold text-[#0d1c2f]">{{ $totalPatients }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-green-600 text-2xl">stethoscope</span>
            </div>
            <div>
                <p class="text-[#526069] text-xs font-medium">Médecins actifs</p>
                <p class="text-3xl font-bold text-[#0d1c2f]">{{ $totalDoctors }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-purple-600 text-2xl">calendar_month</span>
            </div>
            <div>
                <p class="text-[#526069] text-xs font-medium">Total rendez-vous</p>
                <p class="text-3xl font-bold text-[#0d1c2f]">{{ $totalAppointments }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-orange-600 text-2xl">today</span>
            </div>
            <div>
                <p class="text-[#526069] text-xs font-medium">Aujourd'hui</p>
                <p class="text-3xl font-bold text-[#0d1c2f]">{{ $todayAppointments }}</p>
            </div>
        </div>
    </div>

    {{-- ── Second row ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Statuts rendez-vous --}}
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <h3 class="font-bold text-[#0d1c2f] mb-5 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]">pie_chart</span>
                Statuts des rendez-vous
            </h3>
            @php
                $statuses = [
                    ['label' => 'Acceptés',     'count' => $acceptedAppointments,  'bar' => 'bg-green-500',  'text' => 'text-green-700'],
                    ['label' => 'En attente',   'count' => $pendingAppointments,   'bar' => 'bg-yellow-400', 'text' => 'text-yellow-700'],
                    ['label' => 'Annulés',      'count' => $cancelledAppointments, 'bar' => 'bg-red-400',    'text' => 'text-red-700'],
                    ['label' => 'Non réalisés', 'count' => $missedAppointments,    'bar' => 'bg-orange-400', 'text' => 'text-orange-700'],
                ];
                $total = max($totalAppointments, 1);
            @endphp
            <div class="space-y-3.5">
                @foreach($statuses as $s)
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-medium text-[#0d1c2f]">{{ $s['label'] }}</span>
                            <span class="font-bold {{ $s['text'] }}">{{ $s['count'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="{{ $s['bar'] }} h-1.5 rounded-full"
                                 style="width: {{ $total > 0 ? round($s['count'] / $total * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Par spécialité --}}
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <h3 class="font-bold text-[#0d1c2f] mb-5 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]">category</span>
                Par spécialité
            </h3>
            <div class="space-y-3">
                @php $specTotal = max($appointmentsBySpecialty->sum('count'), 1); @endphp
                @forelse($appointmentsBySpecialty->sortByDesc('count')->take(5) as $spec)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-[#0d1c2f] truncate">{{ $spec['name'] }}</span>
                            <span class="font-bold text-[#003f87] ml-2 shrink-0">{{ $spec['count'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-[#003f87] h-1.5 rounded-full"
                                 style="width: {{ round($spec['count'] / $specTotal * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[#526069]">Aucune donnée</p>
                @endforelse
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <h3 class="font-bold text-[#0d1c2f] mb-5 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]">bolt</span>
                Actions rapides
            </h3>
            <div class="space-y-2.5">
                <a href="{{ route('admin.users.create') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl text-sm font-medium text-blue-800 transition-colors">
                    <span class="material-symbols-outlined text-blue-600">person_add</span>
                    Ajouter un utilisateur
                </a>
                <a href="{{ route('admin.doctors.create') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-green-50 hover:bg-green-100 border border-green-200 rounded-xl text-sm font-medium text-green-800 transition-colors">
                    <span class="material-symbols-outlined text-green-600">add_circle</span>
                    Ajouter un médecin
                </a>
                <a href="{{ route('admin.specialties.create') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl text-sm font-medium text-purple-800 transition-colors">
                    <span class="material-symbols-outlined text-purple-600">add_box</span>
                    Ajouter une spécialité
                </a>
                <a href="{{ route('admin.appointments.index') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-[#eff4ff] hover:bg-[#dce8ff] border border-[#c2d4f0] rounded-xl text-sm font-medium text-[#003f87] transition-colors">
                    <span class="material-symbols-outlined text-[#003f87]">manage_search</span>
                    Gérer les rendez-vous
                </a>
            </div>
        </div>
    </div>

    {{-- ── Activité récente ── --}}
    <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]">history</span>
                Rendez-vous récents
            </h3>
            <a href="{{ route('admin.appointments.index') }}"
               class="text-xs font-semibold text-[#003f87] hover:underline">
                Voir tous →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e0e7ff]">
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Patient</th>
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Médecin</th>
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Date</th>
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Statut</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f4ff]">
                    @forelse($recentAppointments as $apt)
                        <tr class="hover:bg-[#f8faff] transition-colors">
                            <td class="py-3 text-sm font-medium text-[#0d1c2f]">{{ $apt->patient->name }}</td>
                            <td class="py-3 text-sm text-[#526069]">{{ $apt->doctor->user->name }}</td>
                            <td class="py-3 text-sm text-[#526069]">{{ $apt->appointment_date->format('d/m/Y') }}</td>
                            <td class="py-3">
                                @php
                                    $badge = match($apt->status) {
                                        'pending'   => 'bg-yellow-100 text-yellow-800',
                                        'accepted'  => 'bg-green-100 text-green-800',
                                        'rejected'  => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-700',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                        'missed'    => 'bg-orange-100 text-orange-800',
                                        default     => 'bg-gray-100 text-gray-700',
                                    };
                                    $statusLabel = match($apt->status) {
                                        'pending'   => 'En attente',
                                        'accepted'  => 'Accepté',
                                        'rejected'  => 'Refusé',
                                        'cancelled' => 'Annulé',
                                        'completed' => 'Terminé',
                                        'missed'    => 'Non réalisé',
                                        default     => ucfirst($apt->status),
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('admin.appointments.show', $apt) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Détails →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-[#526069]">Aucun rendez-vous récent</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
