@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Navigation Admin -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-blue-600">Admin Panel</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg h-screen">
            <div class="p-6">
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.users.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                        Utilisateurs
                    </a>
                    <a href="{{ route('admin.doctors.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.doctors.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                        Médecins
                    </a>
                    <a href="{{ route('admin.specialties.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.specialties.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                        Spécialités
                    </a>
                    <a href="{{ route('admin.appointments.index') }}" class="block px-4 py-2 rounded {{ request()->routeIs('admin.appointments.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                        Rendez-vous
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @yield('admin-content')
        </div>
    </div>
</div>
@endsection
