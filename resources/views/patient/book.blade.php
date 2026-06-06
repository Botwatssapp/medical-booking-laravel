@extends('layouts.app')
@section('title', 'Réserver un RDV')

@section('content')
<div class="max-w-[1440px] mx-auto px-8 py-10">

    {{-- Header --}}
    <div class="mb-10">
        <a href="/patient/doctors" class="flex items-center gap-2 text-[#003f87] hover:underline mb-4">
            <span class="material-symbols-outlined">arrow_back</span>
            Retour aux médecins
        </a>
        <h1 class="text-3xl font-bold text-[#0d1c2f]">Réserver un rendez-vous</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Doctor Info --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-8 border border-[#c2c6d4]/30 shadow-sm sticky top-24">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-24 h-24 rounded-full bg-[#0056b3] text-white flex items-center justify-center text-4xl font-bold mb-4">
                        {{ strtoupper(substr($doctor->user->name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-semibold text-[#0d1c2f]">Dr. {{ $doctor->user->name }}</h2>
                    <p class="text-sm text-[#003f87] font-medium mt-1">{{ $doctor->speciality->name }}</p>
                    @if($doctor->address)
                        <div class="flex items-center gap-1 mt-2 text-[#526069] text-sm">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            {{ $doctor->address }}
                        </div>
                    @endif
                </div>
                @if($doctor->bio)
                    <div class="border-t border-[#c2c6d4] pt-6">
                        <p class="text-sm text-[#424752]">{{ $doctor->bio }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Booking Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl p-8 border border-[#c2c6d4]/30 shadow-sm">
                <h2 class="text-xl font-semibold text-[#0d1c2f] mb-8">Choisir une date et un créneau</h2>

                <form action="/patient/appointments" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">

                    {{-- Date --}}
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-[#0d1c2f]">Date du rendez-vous</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#727784]">calendar_month</span>
                            <input
                                type="date"
                                name="date"
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                required
                                class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#c2c6d4] focus:ring-2 focus:ring-[#003f87] bg-[#f8f9ff] outline-none text-sm"
                            />
                        </div>
                    </div>

                    {{-- Time Slot --}}
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-[#0d1c2f]">Créneau horaire</label>
                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                            @foreach(['09:00','09:30','10:00','10:30','11:00','14:00','14:30','15:00','15:30','16:00'] as $slot)
                                <label class="cursor-pointer">
                                    <input type="radio" name="time_slot" value="{{ $slot }}" class="hidden peer" required>
                                    <div class="py-3 rounded-lg border border-[#c2c6d4] text-sm text-center font-medium transition-all peer-checked:bg-[#003f87] peer-checked:text-white peer-checked:border-[#003f87
