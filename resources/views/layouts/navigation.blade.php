@php
    $u = auth()->user();
    $roleName = $u?->roles?->first()?->name ?? '';
    $roleLabel = match($roleName) {
        'SUPER_ADMIN' => 'Super Admin',
        'FINANCE' => 'Finance',
        'ADMIN_DATA' => 'Admin Data',
        'ADMIN_COLLECTION' => 'Admin Collection',
        'TREASURY' => 'Treasury',
        'PENGAWAS' => 'Pengawas',
        'LEGAL' => 'Legal',
        default => $roleName,
    };
    $roleColor = match($roleName) {
        'SUPER_ADMIN' => 'bg-rose-100 text-rose-700',
        'FINANCE' => 'bg-emerald-100 text-emerald-700',
        'ADMIN_DATA' => 'bg-blue-100 text-blue-700',
        'ADMIN_COLLECTION' => 'bg-amber-100 text-amber-700',
        'TREASURY' => 'bg-purple-100 text-purple-700',
        'PENGAWAS' => 'bg-cyan-100 text-cyan-700',
        'LEGAL' => 'bg-slate-200 text-slate-700',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">

            {{-- Left: Logo + App Name + Home --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.home') }}" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center shrink-0">
                        <span class="text-white text-xs font-bold">KSP</span>
                    </div>
                    <span class="hidden sm:block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">KSP-SMM</span>
                </a>

                <div class="h-5 w-px bg-gray-200 hidden sm:block"></div>

                <a href="{{ route('admin.home') }}"
                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors
                          {{ request()->routeIs('admin.home') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Home
                </a>
            </div>

            {{-- Right: Role badge + User dropdown --}}
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                @if($roleLabel)
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $roleColor }}">
                    {{ $roleLabel }}
                </span>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800 focus:outline-none transition">
                            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-500">{{ strtoupper(substr($u->name ?? $u->email, 0, 1)) }}</span>
                            </div>
                            <span class="hidden md:inline">{{ $u->name }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <div class="text-sm font-medium text-gray-800">{{ $u->name }}</div>
                            <div class="text-xs text-gray-400">{{ $u->email }}</div>
                        </div>
                        <x-dropdown-link :href="route('admin.password.change')">
                            Ganti Password
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="text-red-600 hover:text-red-700 hover:bg-red-50">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Mobile hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('admin.home') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.home') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Home
            </a>
        </div>

        <div class="pt-3 pb-3 border-t border-gray-100 px-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-sm font-bold text-gray-500">{{ strtoupper(substr($u->name ?? $u->email, 0, 1)) }}</span>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-800">{{ $u->name }}</div>
                    <div class="text-xs text-gray-400">{{ $u->email }}</div>
                </div>
                @if($roleLabel)
                <span class="ml-auto px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $roleColor }}">{{ $roleLabel }}</span>
                @endif
            </div>

            <div class="space-y-1">
                <x-responsive-nav-link :href="route('admin.password.change')">
                    Ganti Password
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
