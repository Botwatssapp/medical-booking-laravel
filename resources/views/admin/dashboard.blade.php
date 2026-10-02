@extends('layouts.admin')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', now()->translatedFormat('l j F Y'))

@section('admin-content')
<div class="space-y-6">

    @if($pendingDoctors->isNotEmpty())
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-xl" aria-hidden="true">pending</span>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-amber-900">{{ $pendingDoctorCount }} compte{{ $pendingDoctorCount > 1 ? 's' : '' }} médecin à valider</p>
                    <p class="text-sm text-amber-700">Ces médecins attendent la configuration de leur profil.</p>
                </div>
                <a href="{{ route('admin.doctors.create') }}"
                   class="text-xs font-semibold text-amber-700 hover:text-amber-900 underline underline-offset-2">
                    Voir tous
                </a>
            </div>
            <div class="space-y-2">
                @foreach($pendingDoctors as $pending)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-xl px-4 py-3 border border-amber-200">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-sm shrink-0">
                                {{ strtoupper(substr($pending->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $pending->name }}</p>
                                <p class="text-xs text-gray-500">{{ $pending->email }} · inscrit le {{ $pending->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.doctors.create', ['user_id' => $pending->id]) }}"
                           class="inline-flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors shrink-0">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">check_circle</span>
                            Confirmer
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Utilisateurs', 'value' => $totalUsers, 'icon' => 'group', 'bg' => 'bg-slate-100', 'text' => 'text-slate-700'],
            ['label' => 'Patients', 'value' => $totalPatients, 'icon' => 'person', 'bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
            ['label' => 'Médecins validés', 'value' => $totalDoctors, 'icon' => 'stethoscope', 'bg' => 'bg-green-100', 'text' => 'text-green-600'],
            ['label' => 'En attente', 'value' => $pendingDoctorCount, 'icon' => 'pending', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700'],
            ['label' => 'Rendez-vous', 'value' => $totalAppointments, 'icon' => 'calendar_month', 'bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
            ['label' => 'Aujourd’hui', 'value' => $todayAppointments, 'icon' => 'today', 'bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
            ['label' => 'En attente RDV', 'value' => $pendingAppointments, 'icon' => 'hourglass_top', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
            ['label' => 'Créneaux libres', 'value' => $freeSlots, 'icon' => 'event_available', 'bg' => 'bg-[#eff4ff]', 'text' => 'text-[#003f87]'],
        ] as $stat)
            <div class="bg-white rounded-2xl border border-[#e0e7ff] p-5 flex items-center gap-4">
                <div class="w-12 h-12 {{ $stat['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined {{ $stat['text'] }} text-2xl" aria-hidden="true">{{ $stat['icon'] }}</span>
                </div>
                <div>
                    <p class="text-[#526069] text-xs font-medium">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-bold text-[#0d1c2f]">{{ $stat['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <p class="text-xs text-[#526069]">
        {{ $acceptedAppointments }} confirmé(s) · {{ $completedAppointments }} terminé(s) ·
        {{ $cancelledAppointments }} annulé(s) · {{ $rejectedAppointments }} refusé(s) ·
        {{ $missedAppointments }} non réalisé(s)
        @if($doctorAccounts > $totalDoctors)
            · {{ $doctorAccounts }} comptes médecin
        @endif
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <h2 class="font-bold text-[#0d1c2f] mb-5 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">pie_chart</span>
                Statuts des rendez-vous
            </h2>
            @php
                $statuses = [
                    ['label' => 'Acceptés', 'count' => $acceptedAppointments, 'bar' => 'bg-green-500', 'text' => 'text-green-700'],
                    ['label' => 'En attente', 'count' => $pendingAppointments, 'bar' => 'bg-yellow-400', 'text' => 'text-yellow-700'],
                    ['label' => 'Terminés', 'count' => $completedAppointments, 'bar' => 'bg-blue-400', 'text' => 'text-blue-700'],
                    ['label' => 'Annulés', 'count' => $cancelledAppointments, 'bar' => 'bg-red-400', 'text' => 'text-red-700'],
                    ['label' => 'Refusés', 'count' => $rejectedAppointments, 'bar' => 'bg-rose-400', 'text' => 'text-rose-700'],
                    ['label' => 'Non réalisés', 'count' => $missedAppointments, 'bar' => 'bg-orange-400', 'text' => 'text-orange-700'],
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
                        <div class="w-full bg-gray-100 rounded-full h-1.5" aria-hidden="true">
                            <div class="{{ $s['bar'] }} h-1.5 rounded-full"
                                 style="width: {{ $total > 0 ? round($s['count'] / $total * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <h2 class="font-bold text-[#0d1c2f] mb-5 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">upcoming</span>
                Prochains rendez-vous
            </h2>
            <div class="space-y-3">
                @forelse($upcomingAppointments as $apt)
                    <a href="{{ route('admin.appointments.show', $apt) }}"
                       class="block rounded-xl border border-[#e0e7ff] px-4 py-3 hover:bg-[#f8faff]">
                        <p class="text-sm font-semibold text-[#0d1c2f]">{{ $apt->patient->name }}</p>
                        <p class="text-xs text-[#526069] mt-0.5">
                            Dr {{ $apt->doctor->user->name }}
                            · {{ $apt->appointment_date->format('d/m/Y H:i') }}
                        </p>
                    </a>
                @empty
                    <p class="text-sm text-[#526069]">Aucun rendez-vous à venir.</p>
                    <a href="{{ route('admin.appointments.index') }}" class="inline-block mt-2 text-sm font-semibold text-[#003f87] hover:underline">
                        Voir tous les rendez-vous
                    </a>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
            <h2 class="font-bold text-[#0d1c2f] mb-5 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">bolt</span>
                Actions rapides
            </h2>
            <div class="space-y-2.5">
                <a href="{{ route('admin.users.create') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl text-sm font-medium text-blue-800 transition-colors">
                    <span class="material-symbols-outlined text-blue-600" aria-hidden="true">person_add</span>
                    Ajouter un utilisateur
                </a>
                <a href="{{ route('admin.doctors.create') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-green-50 hover:bg-green-100 border border-green-200 rounded-xl text-sm font-medium text-green-800 transition-colors">
                    <span class="material-symbols-outlined text-green-600" aria-hidden="true">add_circle</span>
                    Valider un médecin
                </a>
                <a href="{{ route('admin.specialties.create') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl text-sm font-medium text-purple-800 transition-colors">
                    <span class="material-symbols-outlined text-purple-600" aria-hidden="true">add_box</span>
                    Ajouter une spécialité
                </a>
                <a href="{{ route('admin.appointments.index') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-[#eff4ff] hover:bg-[#dce8ff] border border-[#c2d4f0] rounded-xl text-sm font-medium text-[#003f87] transition-colors">
                    <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">manage_search</span>
                    Gérer les rendez-vous
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-[#e0e7ff] p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-bold text-[#0d1c2f] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#003f87]" aria-hidden="true">history</span>
                Rendez-vous récents
            </h2>
            <a href="{{ route('admin.appointments.index') }}"
               class="text-xs font-semibold text-[#003f87] hover:underline">
                Voir tous
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="border-b border-[#e0e7ff]">
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Patient</th>
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Médecin</th>
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Date</th>
                        <th class="pb-3 text-left text-xs font-semibold text-[#526069] uppercase tracking-wider">Statut</th>
                        <th class="pb-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f4ff]">
                    @forelse($recentAppointments as $apt)
                        <tr class="hover:bg-[#f8faff] transition-colors">
                            <td class="py-3 text-sm font-medium text-[#0d1c2f]">{{ $apt->patient->name }}</td>
                            <td class="py-3 text-sm text-[#526069]">{{ $apt->doctor->user->name }}</td>
                            <td class="py-3 text-sm text-[#526069]">{{ $apt->appointment_date->format('d/m/Y') }}</td>
                            <td class="py-3">
                                @include('admin.partials.status-badge', ['appointment' => $apt])
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('admin.appointments.show', $apt) }}"
                                   class="text-xs font-semibold text-[#003f87] hover:underline">
                                    Détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-[#526069]">
                                <p class="font-medium text-[#0d1c2f]">Aucun rendez-vous récent</p>
                                <p class="mt-1">Les nouvelles demandes apparaîtront ici.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
