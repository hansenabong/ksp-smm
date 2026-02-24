<x-app-layout>
    <x-slot name="header">Admin Home</x-slot>

    <div class="p-6 bg-white shadow sm:rounded-lg">
        Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->getRoleNames()->join(', ') }})
    </div>
</x-app-layout>