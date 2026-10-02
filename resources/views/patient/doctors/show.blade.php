@extends('layouts.app')
@section('title', 'Dr. '.$doctor->user->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-8 py-8 sm:py-10">

    <a href="{{ route('patient.doctors.index') }}"
       class="text-[#003f87] hover:underline text-sm mb-6 inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_back</span>
        Retour à l'annuaire
    </a>

    <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm p-6 sm:p-8 mb-8">
        <div class="flex flex-col sm:flex-row gap-8 items-start">
            @if($doctor->photo)
                <img src="{{ asset('storage/'.$doctor->photo) }}"
                     alt="Photo de Dr. {{ $doctor->user->name }}"
                     class="w-32 h-32 rounded-xl object-cover shrink-0">
            @elseif($doctor->user->profile_image_url)
                <img src="{{ $doctor->user->profile_image_url }}"
                     alt="Photo de Dr. {{ $doctor->user->name }}"
                     class="w-32 h-32 rounded-xl object-cover shrink-0">
            @else
                <div class="w-32 h-32 rounded-xl bg-[#eff4ff] text-[#003f87] flex items-center justify-center text-4xl font-bold shrink-0" aria-hidden="true">
                    {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1">
                <h1 class="text-2xl font-bold text-[#0d1c2f]">Dr. {{ $doctor->user->name }}</h1>
                <p class="text-[#003f87] font-medium mt-1">{{ $doctor->speciality->name }}</p>

                @if($doctor->bio)
                    <p class="text-[#424752] mt-4 text-sm leading-relaxed">{{ $doctor->bio }}</p>
                @endif

                <div class="mt-4 space-y-2 text-sm text-[#526069]">
                    @if($doctor->phone)
                        <p class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-[#003f87]" aria-hidden="true">phone</span>
                            {{ $doctor->phone }}
                        </p>
                    @endif
                    @if($doctor->address)
                        <p class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-[#003f87]" aria-hidden="true">location_on</span>
                            {{ $doctor->address }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $bookable = $availabilities->where('is_available', true);
    @endphp

    <h2 class="text-xl font-semibold text-[#0d1c2f] mb-4">
        Créneaux disponibles
        <span class="block sm:inline text-sm font-normal text-[#526069] sm:ml-2">
            {{ $bookable->count() }} libre(s)
        </span>
    </h2>

    @if($bookable->isEmpty())
        <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm p-10 text-center text-[#424752]">
            <span class="material-symbols-outlined text-5xl text-[#c2c6d4] block mb-3" aria-hidden="true">event_busy</span>
            <p class="font-medium text-[#0d1c2f]">Aucun créneau libre pour le moment</p>
            <p class="text-sm text-[#526069] mt-1">Ce médecin n’a pas de disponibilité future réservable.</p>
            <a href="{{ route('patient.doctors.index') }}" class="mt-4 inline-block text-[#003f87] hover:underline text-sm">
                Consulter d'autres médecins
            </a>
        </div>
    @else
        @php
            $grouped = $availabilities->groupBy(fn ($a) => $a->date->format('Y-m-d'));
        @endphp

        <div class="space-y-6">
            @foreach($grouped as $date => $slots)
                <div class="bg-white rounded-xl border border-[#c2c6d4]/30 shadow-sm overflow-hidden">
                    <div class="bg-[#eff4ff] px-4 sm:px-6 py-3 border-b border-[#c2c6d4]/30">
                        <p class="font-semibold text-[#003f87]">
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l j F Y') }}
                            <span class="text-xs font-normal text-[#526069] ml-2">
                                {{ $slots->where('is_available', true)->count() }} libre(s)
                            </span>
                        </p>
                    </div>

                    <div class="p-4 sm:p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach($slots as $slot)
                            @if($slot->is_available)
                                <a href="{{ route('patient.appointments.create', ['availability_id' => $slot->id]) }}"
                                   class="flex flex-col items-center p-3 rounded-xl border border-[#003f87]/20 bg-[#f8f9ff]
                                          hover:bg-[#003f87] hover:text-white hover:border-[#003f87] transition-all group text-center"
                                   aria-label="Réserver le {{ $slot->date->format('d/m/Y') }} de {{ substr($slot->start_time, 0, 5) }} à {{ substr($slot->end_time, 0, 5) }}">
                                    <span class="material-symbols-outlined text-[#003f87] group-hover:text-white text-base mb-1" aria-hidden="true">
                                        schedule
                                    </span>
                                    <span class="text-sm font-bold text-[#0d1c2f] group-hover:text-white">
                                        {{ substr($slot->start_time, 0, 5) }}
                                    </span>
                                    <span class="text-xs text-[#526069] group-hover:text-white/80">
                                        – {{ substr($slot->end_time, 0, 5) }}
                                    </span>
                                </a>
                            @else
                                <div class="flex flex-col items-center p-3 rounded-xl border border-[#c2c6d4]/40 bg-[#f1f3f8]
                                            opacity-60 text-center"
                                     aria-disabled="true"
                                     aria-label="Créneau {{ substr($slot->start_time, 0, 5) }} déjà réservé">
                                    <span class="material-symbols-outlined text-[#c2c6d4] text-base mb-1" aria-hidden="true">
                                        event_busy
                                    </span>
                                    <span class="text-sm font-bold text-[#9aa0b0] line-through">
                                        {{ substr($slot->start_time, 0, 5) }}
                                    </span>
                                    <span class="text-xs text-[#9aa0b0] line-through">
                                        – {{ substr($slot->end_time, 0, 5) }}
                                    </span>
                                    <span class="text-[10px] text-[#526069] mt-1 font-medium uppercase tracking-wide">
                                        Réservé
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
