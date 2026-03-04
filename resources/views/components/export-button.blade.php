@props([
    'route',
    'label' => 'Export CSV',
])

<a {{ $attributes->merge([
        'class' => 'h-10 px-4 inline-flex items-center justify-center bg-indigo-600 text-white rounded whitespace-nowrap hover:bg-indigo-700'
    ]) }}
   href="{{ route($route, request()->query()) }}">
    {{ $label }}
</a>