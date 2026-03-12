<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifikasi</h2>
    </x-slot>

    <div class="max-w-2xl space-y-3">

        @if(count($alerts) === 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-10 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-gray-500 text-sm font-medium">Tidak ada notifikasi saat ini.</p>
                <p class="text-gray-400 text-xs mt-1">Semua proses berjalan normal.</p>
            </div>
        @else
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide px-1">
                {{ count($alerts) }} notifikasi aktif
            </p>

            @foreach($alerts as $alert)
                @php
                    $colors = match($alert['level']) {
                        'danger'  => ['bg' => 'bg-red-50',     'border' => 'border-red-200',   'icon' => 'text-red-500',    'btn' => 'bg-red-600 hover:bg-red-700'],
                        'warning' => ['bg' => 'bg-amber-50',   'border' => 'border-amber-200', 'icon' => 'text-amber-500',  'btn' => 'bg-amber-600 hover:bg-amber-700'],
                        default   => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',  'icon' => 'text-blue-500',   'btn' => 'bg-blue-600 hover:bg-blue-700'],
                    };
                @endphp

                <div class="{{ $colors['bg'] }} {{ $colors['border'] }} border rounded-xl px-5 py-4 flex items-start gap-4">
                    <div class="flex-shrink-0 mt-0.5">
                        @if($alert['icon'] === 'clock')
                            <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($alert['icon'] === 'exclamation')
                            <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @elseif($alert['icon'] === 'calculator')
                            <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800">{{ $alert['title'] }}</p>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $alert['message'] }}</p>
                    </div>
                    @if(isset($alert['url']))
                    <div class="flex-shrink-0">
                        <a href="{{ $alert['url'] }}"
                           class="inline-flex items-center px-3 py-1.5 {{ $colors['btn'] }} text-white text-xs font-semibold rounded-lg transition">
                            {{ $alert['label'] ?? 'Buka' }}
                        </a>
                    </div>
                    @endif
                </div>
            @endforeach
        @endif

    </div>
</x-admin-layout>
