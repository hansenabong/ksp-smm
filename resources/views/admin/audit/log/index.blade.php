<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Log Aktivitas</h2>
            <a href="{{ route('admin.audit.log.export', request()->query()) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Dari</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Sampai</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Aksi</label>
                    <select name="action"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">Semua</option>
                        @foreach($actions as $key => $label)
                            <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Modul</label>
                    <select name="subject_type"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">Semua</option>
                        @foreach($subjectTypes as $type)
                            <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Pengguna</label>
                    <select name="user_id"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">Semua</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.audit.log.index') }}"
                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-3 flex items-center gap-2">
            <span class="text-sm text-gray-500">Total:</span>
            <span class="text-sm font-semibold text-gray-800">{{ number_format($logs->total()) }} entri</span>
            @if(request()->hasAny(['date_from','date_to','action','subject_type','user_id']))
                <span class="text-xs text-indigo-600 font-medium ml-2">(terfilter)</span>
            @endif
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Waktu</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pengguna</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Modul</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $log->user?->email ?? '<em class="text-gray-400">system</em>' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($log->action) {
                                            'void'   => 'bg-red-100 text-red-700',
                                            'approve'=> 'bg-green-100 text-green-700',
                                            'reject' => 'bg-orange-100 text-orange-700',
                                            'login'  => 'bg-blue-100 text-blue-700',
                                            'logout' => 'bg-gray-100 text-gray-600',
                                            'create' => 'bg-indigo-100 text-indigo-700',
                                            'update' => 'bg-yellow-100 text-yellow-700',
                                            'delete' => 'bg-red-100 text-red-700',
                                            default  => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if($log->subject_type)
                                        <span class="font-medium">{{ $log->subject_type }}</span>
                                        @if($log->subject_id)
                                            <span class="text-gray-400">#{{ $log->subject_id }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700 max-w-xs">
                                    <span class="line-clamp-2">{{ $log->description ?? '—' }}</span>
                                    @if($log->metadata)
                                        <details class="mt-1">
                                            <summary class="text-xs text-indigo-500 cursor-pointer hover:text-indigo-700">Detail</summary>
                                            <pre class="text-xs text-gray-500 mt-1 whitespace-pre-wrap break-all">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-400 text-xs font-mono whitespace-nowrap">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    Belum ada log aktivitas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $logs->links() }}</div>

    </div>
</x-admin-layout>
