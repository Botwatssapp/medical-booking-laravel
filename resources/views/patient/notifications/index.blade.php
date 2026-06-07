@extends('layouts.app')
@section('title', 'Mes notifications')

@section('content')
<div class="max-w-3xl mx-auto px-8 py-10">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#0d1c2f]">Notifications</h1>
            <p class="text-[#526069] mt-1">Vos alertes et mises à jour de rendez-vous.</p>
        </div>

        @if($notifications->where('read_at', null)->count())
            <form method="POST" action="{{ route('patient.notifications.readAll') }}">
                @csrf @method('PATCH')
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 border border-[#c2c6d4] text-[#526069] rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    <span class="material-symbols-outlined text-base">done_all</span>
                    Tout marquer lu
                </button>
            </form>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4 mb-6">
            <span class="material-symbols-outlined text-green-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Liste --}}
    <div class="space-y-3">
        @forelse($notifications as $notif)
            @php
                $data  = $notif->data;
                $read  = !is_null($notif->read_at);
                $color = $data['color'] ?? 'blue';
                $icon  = $data['icon']  ?? 'notifications';

                $colorMap = [
                    'amber'  => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200',  'icon' => 'text-amber-500'],
                    'red'    => ['bg' => 'bg-red-50',    'border' => 'border-red-200',    'icon' => 'text-red-500'],
                    'green'  => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'icon' => 'text-green-500'],
                    'orange' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'icon' => 'text-orange-500'],
                    'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'icon' => 'text-blue-500'],
                ];
                $c = $colorMap[$color] ?? $colorMap['blue'];
            @endphp

            <div class="bg-white rounded-xl border {{ $read ? 'border-[#c2c6d4]/40' : 'border-[#003f87]/30' }} shadow-sm overflow-hidden
                        {{ !$read ? 'ring-1 ring-[#003f87]/10' : '' }}">
                <div class="flex items-start gap-4 px-5 py-4">

                    {{-- Icône --}}
                    <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} {{ $c['border'] }} border flex items-center justify-center shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-lg {{ $c['icon'] }}">{{ $icon }}</span>
                    </div>

                    {{-- Contenu --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-[#0d1c2f] text-sm">
                                    {{ $data['title'] ?? 'Notification' }}
                                    @if(!$read)
                                        <span class="ml-2 inline-block w-2 h-2 rounded-full bg-[#003f87] align-middle"></span>
                                    @endif
                                </p>
                                <p class="text-sm text-[#526069] mt-0.5 leading-relaxed">
                                    {{ $data['message'] ?? '' }}
                                </p>
                            </div>
                            <span class="text-xs text-[#526069] shrink-0 whitespace-nowrap">
                                {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Détails report --}}
                        @if(isset($data['old_date']) && isset($data['new_date']))
                            <div class="flex items-center gap-3 mt-3 flex-wrap">
                                <span class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-semibold line-through">
                                    {{ $data['old_date'] }} à {{ $data['old_time'] }}
                                </span>
                                <span class="material-symbols-outlined text-[#526069] text-sm">arrow_forward</span>
                                <span class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs font-semibold">
                                    {{ $data['new_date'] }} à {{ $data['new_time'] }}
                                </span>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex items-center gap-3 mt-3">
                            @if(isset($data['url']))
                                <a href="{{ $data['url'] }}"
                                   class="text-xs text-[#003f87] hover:underline font-medium">
                                    Voir mes rendez-vous →
                                </a>
                            @endif

                            @if(!$read)
                                <form method="POST" action="{{ route('patient.notifications.read', $notif->id) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs text-[#526069] hover:text-[#0d1c2f]">
                                        Marquer comme lu
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-[#526069]">
                                    Lu {{ $notif->read_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-16 border border-[#c2c6d4]/30 shadow-sm text-center">
                <span class="material-symbols-outlined text-7xl text-[#c2c6d4] mb-5 block">notifications_off</span>
                <p class="text-[#0d1c2f] text-xl font-bold mb-2">Aucune notification</p>
                <p class="text-[#526069] text-sm">Vous n'avez pas encore de notifications.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>

</div>
@endsection
