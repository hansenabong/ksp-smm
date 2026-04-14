<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buka Deposito Baru</h2>
            <a href="{{ route('admin.deposito.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">
        @if($errors->any())
            <div class="mb-4 flex items-start gap-2 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc ml-3 space-y-0.5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if($depositoProducts->isEmpty())
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
                Belum ada Jenis Simpanan dengan flag <strong>Deposito</strong>. Buat dulu di
                <a href="{{ route('admin.master.saving_products.index') }}" class="underline font-medium">Master Data – Jenis Simpanan</a>.
            </div>
        @endif

        @php
            $productsJs = $depositoProducts->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'rate' => (float)$p->default_interest_rate_pct,
                'term' => (int)$p->default_term_months,
            ])->values();

            $membersJs = $members->map(fn($m) => [
                'id'             => $m->id,
                'member_no'      => $m->member_no,
                'name'           => $m->name,
                'gender'         => $m->gender,
                'ktp_no'         => $m->ktp_no,
                'birth_date'     => $m->birth_date?->format('d/m/Y'),
                'ktp_expiry'     => $m->ktp_expiry?->format('d/m/Y'),
                'marital_status' => $m->marital_status,
                'address'        => $m->address,
                'phone'          => $m->phone,
            ])->values();
        @endphp

        <form method="POST" action="{{ route('admin.deposito.store') }}"
              x-data='{
                  products: @json($productsJs),
                  members: @json($membersJs),
                  productId: "{{ old('saving_product_id') }}",
                  memberId: "{{ old('member_id') }}",
                  startDate: "{{ old('trx_date', now()->toDateString()) }}",
                  amount: "{{ old('amount') }}",
                  get product() { return this.products.find(p => String(p.id) === String(this.productId)) || null; },
                  get member() { return this.members.find(m => String(m.id) === String(this.memberId)) || null; },
                  get maturity() {
                      if (!this.product || !this.startDate) return null;
                      const d = new Date(this.startDate);
                      d.setMonth(d.getMonth() + this.product.term);
                      return d.toISOString().slice(0, 10);
                  },
                  get interest() {
                      if (!this.product || !this.amount) return 0;
                      const a = parseInt(String(this.amount).replace(/[^0-9]/g, "")) || 0;
                      return Math.round(a * (this.product.rate / 100) * (this.product.term / 12));
                  },
                  get total() {
                      const a = parseInt(String(this.amount).replace(/[^0-9]/g, "")) || 0;
                      return a + this.interest;
                  },
                  fmtIdr(n) { return "Rp " + (n||0).toLocaleString("id-ID"); },
                  fmtDate(s) {
                      if (!s) return "—";
                      const d = new Date(s);
                      return d.toLocaleDateString("id-ID", {day:"2-digit", month:"2-digit", year:"numeric"});
                  },
                  genderLabel(g) { return g === "L" ? "Laki-laki" : g === "P" ? "Perempuan" : "—"; },
                  statusLabel(s) {
                      const map = {BELUM_KAWIN:"Belum Kawin",KAWIN:"Kawin",CERAI_HIDUP:"Cerai Hidup",CERAI_MATI:"Cerai Mati"};
                      return map[s] || "—";
                  }
              }'
              class="space-y-4">
            @csrf

            {{-- ============================================================ --}}
            {{-- SECTION 1: ANGGOTA (Member Selection + Data Pribadi Preview) --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="text-sm font-semibold text-gray-700">Data Pribadi</div>
                    <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
                </div>

                <div class="px-5 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Anggota <span class="text-red-500">*</span></label>
                        <select name="member_id" x-model="memberId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                            <option value="">— Pilih Anggota —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" @selected(old('member_id') == $m->id)>
                                    {{ $m->member_no ?? $m->id }} – {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Auto-populated member info preview --}}
                    <div x-show="member" x-cloak class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Info Anggota</div>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nama Lengkap</span>
                                <span class="font-medium text-gray-800" x-text="member?.name || '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Jenis Kelamin</span>
                                <span class="font-medium text-gray-800" x-text="genderLabel(member?.gender)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">No. KTP</span>
                                <span class="font-mono font-medium text-gray-800" x-text="member?.ktp_no || '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tgl. Lahir</span>
                                <span class="font-medium text-gray-800" x-text="member?.birth_date || '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Berlaku KTP</span>
                                <span class="font-medium text-gray-800" x-text="member?.ktp_expiry || 'Seumur Hidup'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status</span>
                                <span class="font-medium text-gray-800" x-text="statusLabel(member?.marital_status)"></span>
                            </div>
                            <div class="col-span-2 flex justify-between">
                                <span class="text-gray-500">Alamat</span>
                                <span class="font-medium text-gray-800 text-right" x-text="member?.address || '—'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Hp</span>
                                <span class="font-medium text-gray-800" x-text="member?.phone || '—'"></span>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">Data di atas diambil dari Master Anggota. Perbarui di menu Master Data jika tidak sesuai.</p>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- SECTION 2: REKENING BANK                                     --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="text-sm font-semibold text-gray-700">Rekening Bank</div>
                    <div class="text-xs text-gray-400 mt-0.5">Rekening tujuan pencairan deposito</div>
                </div>

                <div class="px-5 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Nama Pemilik Rekening</label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                               placeholder="Nama sesuai rekening" maxlength="100">
                        @error('bank_account_name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">No. Rekening</label>
                            <input type="text" name="bank_account_no" value="{{ old('bank_account_no') }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   placeholder="cth: 1234567890" maxlength="50">
                            @error('bank_account_no') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   placeholder="cth: BCA, BRI, Mandiri" maxlength="100">
                            @error('bank_name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- SECTION 3: DETAIL DEPOSITO                                   --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="text-sm font-semibold text-gray-700">Detail Deposito</div>
                </div>

                <div class="px-5 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="trx_date" x-model="startDate"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Produk Deposito <span class="text-red-500">*</span></label>
                            <select name="saving_product_id" x-model="productId"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                                <option value="">— Pilih Produk —</option>
                                @foreach($depositoProducts as $sp)
                                    <option value="{{ $sp->id }}">
                                        {{ $sp->name }} — {{ rtrim(rtrim(number_format((float)$sp->default_interest_rate_pct, 2, ',', '.'), '0'), ',') }}% / {{ $sp->default_term_months }} bln
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Kas Tujuan (Sumber Dana) <span class="text-red-500">*</span></label>
                        <select name="cash_account_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                            <option value="">— Pilih Kas —</option>
                            @foreach($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" @selected(old('cash_account_id') == $acc->id)>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Jumlah Deposito (Rp) <span class="text-red-500">*</span></label>
                        <input type="text" data-money name="amount" x-model="amount"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="0" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Uraian <span class="text-xs text-gray-400 font-normal">(opsional)</span></label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" maxlength="255">
                    </div>

                    {{-- Live preview --}}
                    <div x-show="product" x-cloak class="p-4 bg-indigo-50/50 border border-indigo-100 rounded-lg space-y-1.5 text-xs">
                        <div class="text-xs font-semibold uppercase tracking-wide text-indigo-700 mb-2">Ringkasan Kontrak</div>
                        <div class="flex justify-between"><span class="text-gray-600">Bunga / tahun</span><span class="font-mono" x-text="product ? product.rate + '%' : ''"></span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Jangka waktu</span><span class="font-mono" x-text="product ? product.term + ' bulan' : ''"></span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Tanggal mulai</span><span class="font-mono" x-text="fmtDate(startDate)"></span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Tanggal jatuh tempo</span><span class="font-mono" x-text="fmtDate(maturity)"></span></div>
                        <div class="border-t border-indigo-200 my-2"></div>
                        <div class="flex justify-between"><span class="text-gray-600">Pokok</span><span class="font-mono" x-text="fmtIdr(parseInt(String(amount||'').replace(/[^0-9]/g,'')) || 0)"></span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Bunga (proyeksi saat jatuh tempo)</span><span class="font-mono text-emerald-700" x-text="fmtIdr(interest)"></span></div>
                        <div class="flex justify-between font-semibold"><span>Total payout</span><span class="font-mono" x-text="fmtIdr(total)"></span></div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- SUBMIT                                                       --}}
            {{-- ============================================================ --}}
            <div class="flex gap-2">
                <button type="submit" @if($depositoProducts->isEmpty()) disabled @endif
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white text-sm font-semibold rounded-lg transition">
                    Buka Deposito
                </button>
                <a href="{{ route('admin.deposito.index') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
