<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Anggota — {{ config('app.name', 'KSP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">

<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-indigo-700 tracking-tight">{{ config('app.name', 'KSP') }}</span>
            <span class="text-gray-300">|</span>
            <span class="text-xs text-gray-500 font-medium">Portal Anggota</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-gray-500 hover:text-red-600 transition">Keluar</button>
            </form>
        </div>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Sub-nav --}}
    <div class="flex gap-1 mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-1.5 w-fit">
        <a href="{{ route('portal.dashboard') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                  {{ request()->routeIs('portal.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Beranda
        </a>
        <a href="{{ route('portal.savings') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                  {{ request()->routeIs('portal.savings') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Simpanan
        </a>
        <a href="{{ route('portal.deposito') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                  {{ request()->routeIs('portal.deposito') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Deposito
        </a>
        <a href="{{ route('portal.loans') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                  {{ request()->routeIs('portal.loans') ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Pinjaman
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @yield('content')
</div>

</body>
</html>
