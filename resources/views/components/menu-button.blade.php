@props([
    'href' => '#',
    'variant' => 'primary', // primary|secondary|danger
])

@php
    $base = 'inline-flex items-center justify-center px-3 py-2 rounded text-sm font-medium transition border';
    $variants = [
        'primary' => 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700',
        'secondary' => 'bg-slate-900 text-white border-slate-900 hover:bg-slate-800',
        'danger' => 'bg-red-600 text-white border-red-600 hover:bg-red-700',
    ];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $base . ' ' . $variants[$variant]]) }}>
    {{ $slot }}
</a>
