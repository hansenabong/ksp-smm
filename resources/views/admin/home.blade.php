@php
    $u = auth()->user();

    // ── Role checks for menu visibility ─────────────────────────────────────
    $isSuperAdmin    = $u?->hasRole('SUPER_ADMIN');
    $isFinance       = $u?->hasAnyRole(['SUPER_ADMIN', 'FINANCE']);
    $isAdminData     = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN_DATA']);
    $isAdminColl     = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN_COLLECTION']);
    $isTreasury      = $u?->hasAnyRole(['SUPER_ADMIN', 'TREASURY']);
    $isLegal         = $u?->hasAnyRole(['SUPER_ADMIN', 'LEGAL']);
    $isPengawas      = $u?->hasRole('PENGAWAS');

    // Card visibility
    $showAdminData   = $isAdminData || $isPengawas;
    $showCollection  = $isAdminColl || $isPengawas || $u?->hasAnyRole(['SUPER_ADMIN', 'LEGAL']);
    $showTreasury    = $isTreasury;
    $showLegal       = $isLegal;
    $showFinance     = $isFinance;
    $showSistem      = $isSuperAdmin;

    $notifCount = \App\Http\Controllers\Admin\NotificationController::badgeCount($u);
@endphp

<x-admin-layout>

    <div x-data="{
        currentMenu: null,
        transitioning: false,
        openMenu(menu) {
            this.transitioning = true;
            setTimeout(() => {
                this.currentMenu = menu;
                this.transitioning = false;
            }, 150);
        },
        goBack() {
            this.transitioning = true;
            setTimeout(() => {
                this.currentMenu = null;
                this.transitioning = false;
            }, 150);
        }
    }" class="space-y-5">

        @if(session('success'))
            <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Pending applications alert --}}
        @if($pendingApplicationsCount > 0 && ($isSuperAdmin || $isAdminData))
            <div class="flex items-center justify-between px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span><strong>{{ $pendingApplicationsCount }} pengajuan pinjaman</strong> menunggu persetujuan.</span>
                </div>
                <a href="{{ route('admin.loans.applications.index') }}"
                   class="text-xs font-semibold text-amber-700 hover:text-amber-900 underline underline-offset-2">
                    Lihat Semua &rarr;
                </a>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MAIN MENU VIEW (big department cards)                              --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentMenu === null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             :class="transitioning ? 'opacity-0' : 'opacity-100'"
             class="transition-opacity duration-150">

            {{-- Welcome banner --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Selamat datang, {{ $u->name ?? $u->email }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }} &mdash; Pilih menu untuk memulai</p>
                </div>
                @if($notifCount > 0)
                <a href="{{ route('admin.notifications.index') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 transition text-xs font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    {{ $notifCount }} Notifikasi
                </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- ─── ADMIN DATA ─────────────────────────────────────────── --}}
                @if($showAdminData)
                <button @click="openMenu('admin_data')"
                    class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-left
                           hover:shadow-md hover:border-blue-300 hover:bg-blue-50/30 transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-200 transition-colors">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-blue-700 transition-colors">Admin Data</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Pengajuan pinjaman, data anggota, pinjaman approved</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-400 shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
                @endif

                {{-- ─── ADMIN COLLECTION ───────────────────────────────────── --}}
                @if($showCollection)
                <button @click="openMenu('admin_collection')"
                    class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-left
                           hover:shadow-md hover:border-amber-300 hover:bg-amber-50/30 transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 group-hover:bg-amber-200 transition-colors">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-amber-700 transition-colors">Admin Collection</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Penagihan, DHIA, laporan collection, angsuran</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-amber-400 shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
                @endif

                {{-- ─── TREASURY ───────────────────────────────────────────── --}}
                @if($showTreasury)
                <button @click="openMenu('treasury')"
                    class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-left
                           hover:shadow-md hover:border-purple-300 hover:bg-purple-50/30 transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center shrink-0 group-hover:bg-purple-200 transition-colors">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-purple-700 transition-colors">Treasury</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Cetak PK, kwitansi, permohonan dana, schedule</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-purple-400 shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
                @endif

                {{-- ─── LEGAL ──────────────────────────────────────────────── --}}
                @if($showLegal)
                <button @click="openMenu('legal')"
                    class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-left
                           hover:shadow-md hover:border-slate-400 hover:bg-slate-50/30 transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-200 flex items-center justify-center shrink-0 group-hover:bg-slate-300 transition-colors">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-slate-700 transition-colors">Legal</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Dokumen legal, berkas, pengawasan pinjaman</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-slate-400 shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
                @endif

                {{-- ─── FINANCE ────────────────────────────────────────────── --}}
                @if($showFinance)
                <button @click="openMenu('finance')"
                    class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-left
                           hover:shadow-md hover:border-emerald-300 hover:bg-emerald-50/30 transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0 group-hover:bg-emerald-200 transition-colors">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-emerald-700 transition-colors">Finance</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Kas, simpanan, laporan, akuntansi, audit</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-emerald-400 shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
                @endif

                {{-- ─── SISTEM (SUPER_ADMIN) ───────────────────────────────── --}}
                @if($showSistem)
                <button @click="openMenu('sistem')"
                    class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-left
                           hover:shadow-md hover:border-rose-300 hover:bg-rose-50/30 transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center shrink-0 group-hover:bg-rose-200 transition-colors">
                            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-800 group-hover:text-rose-700 transition-colors">Sistem</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Manajemen pengguna, pengaturan akun</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-rose-400 shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
                @endif

            </div>

            {{-- ─── Utility links (all users) ──────────────────────────────── --}}
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('admin.notifications.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifikasi
                    @if($notifCount > 0)
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600 leading-none">{{ $notifCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.password.change') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Ganti Password
                </a>
            </div>

            {{-- ─── KPI Summary (compact, below menu cards) ────────────────── --}}
            @if($isFinance || $isSuperAdmin)
            <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Anggota Aktif</div>
                    <div class="text-lg font-bold text-gray-900 tabular-nums">{{ number_format($totalActiveMembers, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Saldo Kas</div>
                    <div class="text-lg font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalCashBalance, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Simpanan</div>
                    <div class="text-lg font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalSavings, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Piutang Pinjaman</div>
                    <div class="text-lg font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
                </div>
            </div>
            @endif

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- SUB-MENU VIEWS                                                     --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}

        @php
            $submenuLink = 'flex items-center gap-3 px-4 py-3 rounded-xl bg-white border border-gray-200 ' .
                           'hover:bg-gray-50 hover:border-gray-300 hover:shadow-sm transition-all duration-150 group';
            $submenuIcon = 'w-9 h-9 rounded-lg flex items-center justify-center shrink-0';
            $submenuSection = 'text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-5 mb-2 px-1';
        @endphp

        {{-- ─── ADMIN DATA SUB-MENU ───────────────────────────────────────── --}}
        @if($showAdminData)
        <div x-show="currentMenu === 'admin_data'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             :class="transitioning ? 'opacity-0' : 'opacity-100'"
             class="transition-opacity duration-150">

            {{-- Header --}}
            <div class="flex items-center gap-3 mb-5">
                <button @click="goBack()" class="w-9 h-9 rounded-lg bg-blue-100 hover:bg-blue-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Admin Data</h3>
                    <p class="text-xs text-gray-500">Kelola pengajuan dan data anggota</p>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('admin.loans.applications.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-blue-100">
                        <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-blue-700">Pengajuan Pinjaman</div>
                        <div class="text-xs text-gray-500">Buat dan kelola pengajuan pinjaman</div>
                    </div>
                    @if($pendingApplicationsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">{{ $pendingApplicationsCount }} pending</span>
                    @endif
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.loans.active.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-blue-100">
                        <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-blue-700">Pinjaman Approved</div>
                        <div class="text-xs text-gray-500">Lihat daftar pinjaman yang disetujui</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.master.members.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-blue-100">
                        <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-blue-700">Master Anggota</div>
                        <div class="text-xs text-gray-500">Kelola data anggota koperasi</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif

        {{-- ─── ADMIN COLLECTION SUB-MENU ──────────────────────────────────── --}}
        @if($showCollection)
        <div x-show="currentMenu === 'admin_collection'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             :class="transitioning ? 'opacity-0' : 'opacity-100'"
             class="transition-opacity duration-150">

            <div class="flex items-center gap-3 mb-5">
                <button @click="goBack()" class="w-9 h-9 rounded-lg bg-amber-100 hover:bg-amber-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Admin Collection</h3>
                    <p class="text-xs text-gray-500">Penagihan dan manajemen piutang</p>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('admin.collection.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-amber-100">
                        <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-amber-700">Master Tagihan Nasabah</div>
                        <div class="text-xs text-gray-500">Daftar tagihan dan progress penagihan</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.collection.master-dhia') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-amber-100">
                        <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-amber-700">Data Master DHIA</div>
                        <div class="text-xs text-gray-500">Database Harian Informasi Agunan</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.collection.laporan') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-amber-100">
                        <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-amber-700">Laporan Collection</div>
                        <div class="text-xs text-gray-500">Laporan kolektibilitas dan statistik penagihan</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

            </div>
        </div>
        @endif

        {{-- ─── TREASURY SUB-MENU ──────────────────────────────────────────── --}}
        @if($showTreasury)
        <div x-show="currentMenu === 'treasury'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             :class="transitioning ? 'opacity-0' : 'opacity-100'"
             class="transition-opacity duration-150">

            <div class="flex items-center gap-3 mb-5">
                <button @click="goBack()" class="w-9 h-9 rounded-lg bg-purple-100 hover:bg-purple-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Treasury</h3>
                    <p class="text-xs text-gray-500">Cetak dokumen dan manajemen keuangan</p>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('admin.loans.applications.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-purple-100">
                        <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Cetak Dokumen</div>
                        <div class="text-xs text-gray-500">PK, Penawaran, Kwitansi, Permohonan Dana, Jadwal Angsuran</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.collection.master-dhia') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-purple-100">
                        <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Data Master DHIA</div>
                        <div class="text-xs text-gray-500">Lihat Database Harian Informasi Agunan</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif

        {{-- ─── LEGAL SUB-MENU ─────────────────────────────────────────────── --}}
        @if($showLegal)
        <div x-show="currentMenu === 'legal'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             :class="transitioning ? 'opacity-0' : 'opacity-100'"
             class="transition-opacity duration-150">

            <div class="flex items-center gap-3 mb-5">
                <button @click="goBack()" class="w-9 h-9 rounded-lg bg-slate-200 hover:bg-slate-300 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Legal</h3>
                    <p class="text-xs text-gray-500">Dokumen legal dan pengawasan pinjaman</p>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('admin.legal.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-slate-200">
                        <svg class="w-4.5 h-4.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-slate-700">Dokumen Legal</div>
                        <div class="text-xs text-gray-500">Upload dan kelola berkas legal</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.collection.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-slate-200">
                        <svg class="w-4.5 h-4.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-slate-700">Master Tagihan</div>
                        <div class="text-xs text-gray-500">Lihat daftar tagihan nasabah</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.collection.master-dhia') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-slate-200">
                        <svg class="w-4.5 h-4.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-slate-700">Data Master DHIA</div>
                        <div class="text-xs text-gray-500">Database Harian Informasi Agunan</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.master.members.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-slate-200">
                        <svg class="w-4.5 h-4.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-slate-700">Master Anggota</div>
                        <div class="text-xs text-gray-500">Lihat data anggota koperasi</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif

        {{-- ─── FINANCE SUB-MENU ───────────────────────────────────────────── --}}
        @if($showFinance)
        <div x-show="currentMenu === 'finance'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             :class="transitioning ? 'opacity-0' : 'opacity-100'"
             class="transition-opacity duration-150">

            <div class="flex items-center gap-3 mb-5">
                <button @click="goBack()" class="w-9 h-9 rounded-lg bg-emerald-100 hover:bg-emerald-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Finance</h3>
                    <p class="text-xs text-gray-500">Kas, simpanan, laporan, dan akuntansi</p>
                </div>
            </div>

            <div class="space-y-1">

                {{-- KAS --}}
                <div class="{{ $submenuSection }}">Kas</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('admin.cash.balances.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-emerald-100">
                            <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Saldo Kas</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.cash.income.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-green-100">
                            <svg class="w-4.5 h-4.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Pemasukan</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.cash.expense.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-red-100">
                            <svg class="w-4.5 h-4.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Pengeluaran</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.cash.transfer.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-blue-100">
                            <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Transfer</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                {{-- SIMPANAN --}}
                <div class="{{ $submenuSection }}">Simpanan</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('admin.savings.deposit.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-indigo-100">
                            <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Setoran</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.savings.withdraw.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-indigo-100">
                            <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Penarikan</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.savings.member_balance.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-indigo-100">
                            <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Saldo Anggota</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                {{-- LAPORAN --}}
                <div class="{{ $submenuSection }}">Laporan</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('admin.reports.cash') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-teal-100">
                            <svg class="w-4.5 h-4.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Laporan Kas</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.reports.savings') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-teal-100">
                            <svg class="w-4.5 h-4.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Laporan Simpanan</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.reports.loans') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-teal-100">
                            <svg class="w-4.5 h-4.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Laporan Pinjaman</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                {{-- MASTER DATA --}}
                <div class="{{ $submenuSection }}">Master Data</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('admin.master.cash_categories.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-orange-100">
                            <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Kategori Kas</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.master.cash_accounts.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-orange-100">
                            <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Kas (Rekening)</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.master.members.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-orange-100">
                            <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Anggota</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.master.saving_products.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-orange-100">
                            <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Jenis Simpanan</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.master.loan_products.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-orange-100">
                            <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Jenis Pinjaman</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                {{-- AUDIT --}}
                <div class="{{ $submenuSection }}">Audit</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('admin.audit.log.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-rose-100">
                            <svg class="w-4.5 h-4.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Log Aktivitas</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.audit.void.cash') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-rose-100">
                            <svg class="w-4.5 h-4.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">VOID Kas</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.audit.void.savings') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-rose-100">
                            <svg class="w-4.5 h-4.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">VOID Simpanan</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.audit.void.loan_payments') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-rose-100">
                            <svg class="w-4.5 h-4.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">VOID Angsuran</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                {{-- AKUNTANSI --}}
                <div class="{{ $submenuSection }}">Akuntansi</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('admin.accounting.accounts.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-violet-100">
                            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Bagan Akun (COA)</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.accounting.income_statement.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-violet-100">
                            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Laba Rugi</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.accounting.balance_sheet.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-violet-100">
                            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Neraca</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.accounting.trial_balance.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-violet-100">
                            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Trial Balance</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.accounting.general_ledger.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-violet-100">
                            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Buku Besar</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.accounting.journal_entries.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-violet-100">
                            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">Jurnal</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.shu.index') }}" class="{{ $submenuLink }}">
                        <div class="{{ $submenuIcon }} bg-violet-100">
                            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                        </div>
                        <div class="flex-1"><div class="text-sm font-semibold text-gray-800">SHU</div></div>
                        <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

            </div>
        </div>
        @endif

        {{-- ─── SISTEM SUB-MENU ────────────────────────────────────────────── --}}
        @if($showSistem)
        <div x-show="currentMenu === 'sistem'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             :class="transitioning ? 'opacity-0' : 'opacity-100'"
             class="transition-opacity duration-150">

            <div class="flex items-center gap-3 mb-5">
                <button @click="goBack()" class="w-9 h-9 rounded-lg bg-rose-100 hover:bg-rose-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Sistem</h3>
                    <p class="text-xs text-gray-500">Manajemen pengguna dan pengaturan</p>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('admin.users.index') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-rose-100">
                        <svg class="w-4.5 h-4.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-rose-700">Manajemen User</div>
                        <div class="text-xs text-gray-500">Kelola akun pengguna dan role</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.password.change') }}" class="{{ $submenuLink }}">
                    <div class="{{ $submenuIcon }} bg-rose-100">
                        <svg class="w-4.5 h-4.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-rose-700">Ganti Password</div>
                        <div class="text-xs text-gray-500">Ubah password akun Anda</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif

    </div>
</x-admin-layout>
