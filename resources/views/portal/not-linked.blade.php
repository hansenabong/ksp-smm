@extends('layouts.portal')

@section('content')
<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-8 py-10 max-w-md w-full">
        <div class="text-4xl mb-4">⚠️</div>
        <h2 class="text-lg font-bold text-gray-800 mb-2">Akun Belum Terhubung</h2>
        <p class="text-sm text-gray-500 mb-6">
            Akun Anda belum terhubung ke data anggota.<br>
            Hubungi administrator untuk menghubungkan akun Anda.
        </p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Keluar
            </button>
        </form>
    </div>
</div>
@endsection
