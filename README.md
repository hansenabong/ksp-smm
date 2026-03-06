# KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam

## Overview

KSP-SMM is a web-based financial management system for a savings & loan cooperative (Koperasi Simpan Pinjam). It handles member management, multi-account cash operations, savings products, loan lifecycle (application → approval → disbursement → payments → closure), penalty calculations, notaris deed tracking, disbursement receipts (kwitansi), and double-entry accounting with automated journal posting.

---

## Development Progress

| # | Milestone | Status |
|---|-----------|--------|
| 1 | Core foundation — auth (Breeze), member CRUD, cash accounts, basic cash transactions | ✅ Done |
| 2 | Savings module — products, deposit/withdraw, void, GL auto-posting | ✅ Done |
| 3 | Loan lifecycle — application, approval, disbursement, installment payments, void, PDFs | ✅ Done |
| 4 | Accounting engine — 5-level COA, double-entry PostingService, AutoPostingService, Trial Balance | ✅ Done |
| 5 | Penalty & early-repayment rates — basis-point config, Alpine.js reactive UI on payment form | ✅ Done |
| 6 | Notaris deed tracking — optional notaris fields on loan applications, collapsible form section | ✅ Done |
| 7 | User management — CRUD, role assignment, gate-based access, public register disabled | ✅ Done |
| 8 | Cash categories with GL mapping — income/expense/transfer categories linked to COA, auto-offset in posting | ✅ Done |
| 9 | CashAccount ↔ ledger auto-link — CASH→111x / BANK→112x leaf accounts auto-generated on create | ✅ Done |
| 10 | Reporting & audit — Cash/Savings/Loans reports (CSV export), Void Audit trail (CSV export) | ✅ Done |
| 11 | Financial statements — Trial Balance with date-range filter, Income Statement, Balance Sheet | ✅ Done |
| 12 | Dashboard KPIs — real-time cash balances, active loans, outstanding principal widgets | ✅ Done |
| 13 | SHU (Sisa Hasil Usaha) — period management, auto-calculation, member distribution, GL finalization | ✅ Done |
| 14 | Single-session enforcement — one active session per account, auto-logout on new login | ✅ Done |
| 15 | Loan application edit — editable while PENDING (all fields + files); SUPER_ADMIN can correct personal data on APPROVED applications | ✅ Done |
| 16 | Demo seeders — `DemoTransactionSeeder` (6-month realistic data), `MasterConfigSeeder` (fast config-only setup) | ✅ Done |
| 17 | Loan settlement calculator — pelunasan dipercepat with denda, PDF summary, date picker | ✅ Done |
| 18 | Loan sub-ledger PDF — per-loan statement (disbursement + all payments, outstanding balance, flag L/O) | ✅ Done |
| 19 | Kwitansi Plafon — printable standard Indonesian receipt (terbilang, PK number, meterai placeholder) | ✅ Done |
| 20 | Kwitansi Pencairan — disbursement receipt with 13 editable biaya fields stored as JSON; net dana diterima | ✅ Done |
| 21 | Permohonan Dana — internal disbursement request form (reuses biaya data, printable) | ✅ Done |
| 22 | Cetak Semua PDF — bulk print for Pengeluaran and Transfer kas (mirrors existing Pemasukan feature) | ✅ Done |
| 23 | Role matrix refactor (Finance/Accounting standard) — ADMIN config-only; BENDAHARA approve/void; MANAGER approve loans; PETUGAS entry | ✅ Done |
| 24 | PK B (Kendaraan) loan agreement — vehicle collateral PDF template (Pasal 3 with 11 vehicle spec fields), separate from PK A (Sertipikat) | ✅ Done |
| 25 | Loan show page UI — "Cetak Dokumen" dropdown consolidates all print actions; primary action buttons (Bayar Angsuran, Pelunasan) remain visible | ✅ Done |

---

## Planned Milestones

| # | Milestone | Priority |
|---|-----------|----------|
| 26 | **COA Management UI** — CRUD for Chart of Accounts (add/edit/deactivate accounts), restricted to SUPER_ADMIN | High |
| 27 | **Member Savings Balance View** — per-member savings summary across all products, printable passbook-style | High |
| 28 | **Loan Disbursement Posting Fix** — add `disbursement_fees` deductions to GL posting (currently only principal is posted) | High |
| 29 | **Loan Application Agreement v2** — incorporate notaris deed number, disbursement fees table, and borrower signatures into the PDF | Medium |
| 30 | **Notification System** — in-app alerts for pending approvals, overdue payments, SHU finalization | Medium |
| 31 | **Member Portal** — read-only self-service for members: view own savings, loan status, payment history | Medium |
| 32 | **Collateral Registry** — track agunan (collateral) assets per loan: type, value, document, release workflow | Medium |
| 33 | **Bulk Simpanan Wajib** — monthly mandatory savings batch-entry for all active members in one form | Medium |
| 34 | **Print Statements — Cash Balances** — printable cash balance report per account with running balance | Low |
| 35 | **API v1 Expansion** — REST endpoints for loan applications (currently partial), member data, payments | Low |
| 36 | **2FA / OTP Login** — optional TOTP second factor for SUPER_ADMIN and ADMIN accounts | Low |
| 37 | **Audit Log** — comprehensive action log (who changed what, when) beyond current void-only audit trail | Low |
| 38 | **Automated Test Coverage** — Pest feature tests for critical workflows (loan lifecycle, cash void, SHU finalization) | Low |

---

## Tech Stack

| Layer      | Technology                                           |
|------------|------------------------------------------------------|
| Backend    | PHP 8.2+, Laravel 12                                 |
| Database   | PostgreSQL 15+                                       |
| Frontend   | Blade, Tailwind CSS 3, Alpine.js 3                   |
| Bundler    | Vite 7                                               |
| Auth/RBAC  | Laravel Breeze + Spatie Permission 7                  |
| PDF        | barryvdh/laravel-dompdf 3                            |
| Testing    | Pest 4                                               |

---

## Directory Structure

```
ksp/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/               # 28 controllers
│   │   │   ├── Master/          #   CashAccount, CashCategory, Member, SavingProduct, LoanProduct
│   │   │   ├── Reports/         #   Cash, Savings, Loans reports (+ CSV export)
│   │   │   ├── Audit/           #   VoidAuditController (+ CSV export)
│   │   │   ├── Accounting/      #   GeneralLedger, JournalEntry, IncomeStatement, BalanceSheet, SHU
│   │   │   ├── AdminHomeController
│   │   │   ├── UserController
│   │   │   ├── Cash*Controller  #   Income, Expense, Balance, Transfer, Void
│   │   │   ├── Saving*Controller#   Deposit, Withdraw, Void
│   │   │   └── Loan*Controller  #   Application, Loan (+ settlement/kwitansi/permohonan), Payment, PaymentVoid
│   │   ├── Auth/                # 8 Breeze authentication controllers
│   │   └── Api/V1/              # 1 API controller (loan applications)
│   ├── Models/                  # 15 Eloquent models
│   ├── Services/                # 5 business-logic services
│   │   ├── Accounting/          #   PostingService, AutoPostingService
│   │   ├── Cash/                #   CashTransactionService
│   │   ├── Loans/               #   LoanService
│   │   └── Savings/             #   SavingsService
│   ├── Policies/                # MemberPolicy, LoanApplicationPolicy
│   └── helpers.php              # terbilang() — number to Indonesian words
├── resources/views/             # 55+ Blade templates
│   └── admin/
│       ├── accounting/          #   trial_balance/, general_ledger/, journal_entries/, income_statement/, balance_sheet/, shu/
│       ├── audit/               #   void/ (cash, savings, loan_payments)
│       ├── cash/                #   balances, expense, income, transfer, void (each with print-all PDF view)
│       ├── loans/               #   active/ (show, schedule, settlement, kwitansi-plafon, kwitansi-pencairan, permohonan-dana, subledger PDF)
│       │                        #   applications/ (index, show, create, edit, edit-personal, agreement PDF, agreement-vehicle PDF)
│       │                        #   payments/ (create, receipt PDF)
│       ├── master/              #   cash_accounts, cash_categories, loan_products, members, saving_products
│       ├── reports/             #   cash, loans, savings
│       ├── savings/             #   deposit, withdraw, void
│       └── users/               #   create, edit, index
├── routes/
│   ├── web.php                  # All admin routes (role-gated per finance matrix)
│   └── auth.php                 # Auth routes (public /register disabled)
├── database/
│   ├── migrations/              # 38+ migration files
│   └── seeders/                 # RolesSeeder, SuperAdminSeeder, UsersSeeder, DemoMasterSeeder, AccountingSeeder, CashCategorySeeder, DemoTransactionSeeder, MasterConfigSeeder
└── config/                      # 11 config files
```

---

## Data Models

### Core Entities

| Model            | Purpose                                                             |
|------------------|---------------------------------------------------------------------|
| `User`           | System users with Spatie roles                                      |
| `Member`         | Cooperative members (member_no, name, phone, NIK, address, active) |
| `CashAccount`    | Cash accounts (name, type CASH/BANK, ledger auto-linked, active)   |
| `CashCategory`   | Cash transaction categories (type INCOME/EXPENSE/TRANSFER, linked GL account) |
| `SavingProduct`  | Savings product definitions (interest rate, withdrawable flag)      |
| `LoanProduct`    | Loan products (interest rate, admin fee, tenor, penalty rates, linked GL accounts) |

### Transaction Entities

| Model                | Purpose                                                              |
|----------------------|----------------------------------------------------------------------|
| `CashTransaction`    | Cash income/expense/transfer with category and void tracking         |
| `SavingTransaction`  | Member savings deposits/withdrawals with void tracking               |
| `LoanApplication`    | Loan applications with approval workflow, form data snapshot, notaris details, file attachments |
| `LoanApplicationFile`| Document attachments for loan applications (type, path, filename)   |
| `Loan`               | Approved/active loans with payment schedule, outstanding balance, and `disbursement_fees` JSON |
| `LoanPayment`        | Installment payments with penalty fields and void tracking           |

### Accounting Entities

| Model          | Purpose                                                              |
|----------------|----------------------------------------------------------------------|
| `Account`      | Chart of Accounts — 5-level hierarchy (code, name, type, dc, parent)|
| `JournalEntry` | Accounting journal entries (source-tracked, source_type/source_id)   |
| `JournalLine`  | Individual debit/credit lines (account_id, dc, amount)              |

---

## Chart of Accounts (COA)

Seeded by `AccountingSeeder`. Five-level hierarchy:

| Code Range | Category                        | Normal Balance |
|------------|---------------------------------|----------------|
| 1xxx       | Assets (Aktiva)                 | Debit          |
| 1110       | Kas (Cash accounts group)       | Debit          |
| 1120       | Bank accounts group             | Debit          |
| 1130       | Piutang Pinjaman (per product)  | Debit          |
| 2xxx       | Liabilities (Kewajiban)         | Credit         |
| 2110       | Simpanan Anggota (per product)  | Credit         |
| 3xxx       | Equity (Modal)                  | Credit         |
| 4xxx       | Revenue (Pendapatan)            | Credit         |
| 4111       | Pendapatan Bunga Pinjaman       | Credit         |
| 4112       | Pendapatan Administrasi         | Credit         |
| 4113       | Pendapatan Asuransi             | Credit         |
| 4114       | Denda Keterlambatan             | Credit         |
| 4115       | Denda Pelunasan Cepat           | Credit         |
| 5xxx       | Expenses (Beban)                | Debit          |

Dynamic account linking:
- `CashAccount` (CASH type) → leaf auto-generated under 1110 (111x); (BANK type) → leaf under 1120 (112x)
- `SavingProduct` → `ledger_account_id` leaf under 2110
- `LoanProduct` → `receivable_account_id` (under 1130) + `interest_income_account_id`, `admin_income_account_id`, `insurance_income_account_id` (4111–4113)
- `CashCategory` (INCOME) → `account_id` leaf under equity (3xxx) or revenue (4xxx)
- `CashCategory` (EXPENSE) → `account_id` leaf under expense (5xxx)
- `CashCategory` (TRANSFER) → no offset account needed (both sides are cash ledger accounts)

---

## Service Architecture

### PostingService
- Creates balanced double-entry journal entries
- Validates debit = credit before persisting
- Validates non-empty lines and valid `dc` (D/C) values
- Voids journal entries by source type/ID

### AutoPostingService
- Automatically generates GL journal entries for:
  - Cash transactions (INCOME/EXPENSE/TRANSFER)
  - Savings deposits/withdrawals
  - Loan disbursements (DR Piutang, CR Kas)
  - Loan installment payments (DR Kas, CR Piutang/Revenue)
- `resolveOffsetAccount()` — offset account priority: 1) `cash_category.account_id`, 2) hardcoded COA fallback
- Skips system categories (handled by respective modules)
- Guards against duplicate posting (excludes voided entries)
- Maps cash accounts to ledger accounts via `CashAccount.ledger_account_id`
- Posts penalties to 4114 (denda keterlambatan) and 4115 (denda pelunasan cepat)

### CashTransactionService
- Creates income/expense/transfer transactions
- Generates sequential transaction codes (`CK-YYYYMM-XXXXXX`)
- Balance check before expense/transfer (prevents negative balance)
- `getBalance()` — per-account balance from non-voided transactions
- `getAllBalances()` — single-query CTE for dashboard balances

### LoanService
- Approves and disburses loans from applications
- Calculates flat-rate components (principal, interest, admin, insurance)
- Monthly principal = `intdiv(principal, tenor)`, last month absorbs remainder
- **Penalty calculation:**
  - Late payment: `penalty_daily_rate_bp` (basis points, default 30 = 0.30%/day) × `penalty_days` × outstanding
  - Early repayment: `early_repayment_rate_bp` (default 500 = 5%) × outstanding principal
  - Both auto-computed, can be overridden from the payment form
- Overpayment guard — payment cannot exceed outstanding balance
- Payment voiding cascades: voids payment → cash tx → journal entries
- Recomputes loan totals from non-voided payments
- Auto-closes loan when outstanding = 0

### SavingsService
- Processes deposits and withdrawals
- Balance check before withdrawal
- Creates corresponding cash transactions (system category)
- Voiding cascades: voids saving tx → cash tx → journal entries

---

## Authorization

### Role Matrix (Finance/Accounting Standard)

| Role          | Responsibility |
|---------------|----------------|
| `SUPER_ADMIN` | Full system access including user/role management and accounting structure |
| `ADMIN`       | Configuration only — master data CRUD + user management (PETUGAS only) + reporting view |
| `MANAGER`     | Operational oversight — approve/reject loans, monitor reports, view accounting |
| `BENDAHARA`   | Treasury — all transaction entry (kas, simpanan, loan payments), approve/void, reports |
| `PETUGAS`     | Data entry — create transactions (kas, simpanan, loan payments), submit loan applications |

### Access Matrix

| Module / Action | SUPER_ADMIN | ADMIN | MANAGER | BENDAHARA | PETUGAS |
|-----------------|:-----------:|:-----:|:-------:|:---------:|:-------:|
| **Kas** — input pemasukan/pengeluaran/transfer | ✓ | — | — | ✓ | ✓ |
| **Kas** — lihat saldo | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Kas** — void transaksi | ✓ | — | — | ✓ | — |
| **Simpanan** — setor / tarik | ✓ | — | — | ✓ | ✓ |
| **Simpanan** — void | ✓ | — | — | ✓ | — |
| **Pinjaman** — buat pengajuan | ✓ | — | ✓ | — | ✓ |
| **Pinjaman** — edit pengajuan (PENDING) | ✓ | — | ✓ | — | own only |
| **Pinjaman** — edit data pribadi (APPROVED) | ✓ | — | — | — | — |
| **Pinjaman** — setujui / tolak | ✓ | — | ✓ | — | — |
| **Pinjaman** — bayar angsuran | ✓ | — | — | ✓ | ✓ |
| **Pinjaman** — void angsuran | ✓ | — | — | ✓ | — |
| **Pinjaman** — lihat aktif, kwitansi, settlement | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Akuntansi / Laporan / Audit** | ✓ | ✓ | ✓ | ✓ | — |
| **SHU** — lihat | ✓ | ✓ | ✓ | ✓ | — |
| **SHU** — buat / finalisasi | ✓ | — | ✓ | — | — |
| **Master Data** | ✓ | ✓ | — | — | — |
| **Kelola Pengguna** (SUPER_ADMIN + ADMIN + MANAGER + BENDAHARA) | ✓ | — | — | — | — |
| **Kelola Pengguna** (PETUGAS only) | ✓ | ✓ | — | — | — |

Policy rules:
- `MemberPolicy::viewSensitive` — all roles except BENDAHARA
- `LoanApplicationPolicy::viewSensitiveData` — SUPER_ADMIN/MANAGER always; PETUGAS only if `created_by === auth()->id()`
- `LoanApplicationPolicy::approve` — SUPER_ADMIN/MANAGER
- Gates in `AppServiceProvider`: `view-sensitive-member-data`, `create-user`
- Edit PENDING application — SUPER_ADMIN/MANAGER always; PETUGAS only if `created_by === auth()->id()`
- Edit APPROVED personal info — SUPER_ADMIN only (financial terms are permanently locked after approval)
- `UserController::canManage()` — ADMIN gets HTTP 403 if editing/deleting a user with role outside PETUGAS

---

## Key Workflows

### Cash Transaction Flow
1. User creates income/expense/transfer via controller
2. Controller validates input → calls `CashTransactionService`
3. Service creates `CashTransaction` with temp UUID code
4. Updates code to sequential format using record ID
5. `AutoPostingService::resolveOffsetAccount()` finds the GL offset account from the cash category
6. `AutoPostingService` creates journal entry (skips system categories)

### Loan Lifecycle
1. **Application** — officer submits `LoanApplication` with form data snapshot, documents, optional notaris deed details
2. **Edit (PENDING)** — any eligible user can update personal data, loan terms, and attach/delete documents while status is PENDING
   - PETUGAS can only edit their own applications (`created_by === auth()->id()`)
3. **Approval** — manager approves → `LoanService::approveAndDisburse()`
   - Creates cash expense (disbursement)
   - Creates `Loan` record with calculated schedule
   - Posts GL journal (DR Piutang, CR Kas)
4. **Kwitansi & Permohonan Dana** — print-ready documents: Kwitansi Plafon, Kwitansi Pencairan (with biaya-biaya JSON), Permohonan Dana
5. **Payments** — `LoanService::payInstallment()`
   - Auto-computes late penalty and early repayment penalty
   - Creates cash income, posts GL journal
   - Auto-closes loan when outstanding = 0
6. **Settlement (Pelunasan)** — calculator page with date picker; generates PDF breakdown
7. **Void** — reverses payment + cash tx + journal entries

### Void Audit Trail
All voidable entities track: `is_voided`, `void_reason`, `voided_by`, `voided_at`.
Void operations cascade through linked records (transaction → cash → journal).

---

## PDF Generation

| Document | Route | Engine |
|----------|-------|--------|
| Loan Agreement PK A (Sertipikat) | `/loans/applications/{id}/agreement` | DomPDF |
| Loan Agreement PK B (Kendaraan) | `/loans/applications/{id}/agreement-vehicle` | DomPDF |
| Payment Receipt | `/loans/payments/{id}/receipt` | DomPDF |
| Loan Schedule | `/loans/active/{id}/schedule/pdf` | DomPDF |
| Settlement PDF | `/loans/active/{id}/settlement/pdf` | DomPDF |
| Sub-Ledger PDF | `/loans/active/{id}/subledger/pdf` | DomPDF |
| Kwitansi Plafon | `/loans/active/{id}/kwitansi-plafon` | `window.print()` |
| Kwitansi Pencairan | `/loans/active/{id}/kwitansi-pencairan` | `window.print()` |
| Permohonan Dana | `/loans/active/{id}/permohonan-dana` | `window.print()` |
| Cetak Semua Pemasukan | `/cash/income/print` | DomPDF |
| Cetak Semua Pengeluaran | `/cash/expense/print` | DomPDF |
| Cetak Semua Transfer | `/cash/transfer/print` | DomPDF |

---

## SHU (Sisa Hasil Usaha)

1. **Buat Periode** — tentukan tahun, rentang tanggal, dan persentase alokasi (7 bucket dalam basis poin, total = 10.000 bp = 100%)
2. **Kalkulasi Otomatis** — `shu_bruto` dihitung dari jurnal REVENUE − EXPENSE dalam periode
3. **Distribusi per Anggota** — proporsional berdasarkan rata-rata simpanan dan total angsuran pinjaman
4. **Finalisasi** — posting jurnal otomatis: DR 3212 | CR ekuitas/liabilitas per bucket
5. **Recalculate** — hitung ulang selama masih DRAFT

| Bucket         | Akun COA              |
|----------------|-----------------------|
| Cadangan Umum  | 3112                  |
| Jasa Simpanan  | 2130                  |
| Jasa Usaha     | (distribusi anggota)  |
| Dana Pengurus  | 3213                  |
| Dana Pegawai   | 3214                  |
| Dana Sosial    | 3215                  |
| Dana Pendidikan| 3216                  |

---

## Security

### Implemented

| Control | Implementation |
|---------|---------------|
| Authentication | Laravel Breeze with session-based auth |
| Single-session enforcement | `session_token` column + `EnforceSingleSession` middleware; new login invalidates prior session |
| CSRF protection | Laravel default — all POST/PUT/PATCH/DELETE forms include `@csrf` |
| Role-based access control | Spatie Permission v7; every admin route group has explicit `role:` middleware |
| Password hashing | `bcrypt` via Laravel `Hash` facade |
| XSS prevention | All Blade output uses `{{ }}` (auto-escaped); no `{!! !!}` in admin views |
| SQL injection prevention | All queries use Eloquent/Query Builder bindings; `selectRaw` calls use hardcoded SQL only |
| File upload validation | `mimes:jpg,jpeg,png,pdf`, `max:5120` (5 MB); stored on `private` disk (not web-accessible) |
| Mass assignment protection | All models use explicit `$fillable` arrays |
| Privilege escalation guard | `UserController::canManage()` — ADMIN cannot edit/delete MANAGER/BENDAHARA/ADMIN users |
| Public registration disabled | `/register` route removed in `routes/auth.php` |
| File ownership check | `deleteFile()` and `downloadFile()` verify `loan_application_id === application->id`; PETUGAS ownership enforced on `show()`, `deleteFile()`, `downloadFile()` |
| MIME type integrity | File uploads store MIME from `getMimeType()` (reads actual file content via `finfo`), not client-supplied header |
| Rate limiting — profile/password | `PATCH /profile` throttle:10/min, `DELETE /profile` throttle:5/min, `PUT /password` throttle:5/min |

### Known Issues

None — all previously identified issues have been resolved.

---

## Reporting & Audit

| Report              | Filters                         | Export |
|---------------------|---------------------------------|--------|
| Cash Report         | date range, account, type       | CSV    |
| Savings Report      | date range, product, type       | CSV    |
| Loans Report        | date range, product, status     | CSV    |
| Trial Balance       | date range                      | —      |
| Income Statement    | date range                      | —      |
| Balance Sheet       | as-of date                      | —      |
| Void Audit — Cash   | date range                      | CSV    |
| Void Audit — Savings| date range                      | CSV    |
| Void Audit — Loans  | date range                      | CSV    |

---

## Development Setup

```bash
# Full setup
composer run setup          # composer install, npm install, migrate, build

# Development server
composer run dev            # concurrent: artisan serve + queue + npm dev

# Testing
composer run test           # Pest test suite
```

**Environment:** PostgreSQL on `localhost:5432`, database `ksp_smm`, app URL `http://localhost:8000`

### Seed the database

```bash
# Option A — Quick setup (master data + config only, no demo transactions)
php artisan db:seed --class=MasterConfigSeeder

# Option B — Full demo setup (master data + 6 months of transactions)
php artisan db:seed
```

Or run individual seeders (must follow this order):

```bash
php artisan db:seed --class=RolesSeeder           # 1. Create Spatie roles
php artisan db:seed --class=UsersSeeder           # 2. Create 10 test accounts
php artisan db:seed --class=DemoMasterSeeder      # 3. Members, cash accounts, products
php artisan db:seed --class=AccountingSeeder      # 4. Full COA + ledger auto-linking
php artisan db:seed --class=CashCategorySeeder    # 5. Cash categories with GL accounts
php artisan db:seed --class=DemoTransactionSeeder # 6. 6 months of demo transactions (optional)
```

> All seeders are idempotent — safe to re-run without creating duplicates.

### Demo Accounts

| Email                  | Password            | Role        |
|------------------------|---------------------|-------------|
| superadmin@ksp.local   | SuperAdmin@KSP2025  | SUPER_ADMIN |
| admin@ksp.local        | Admin@KSP2025       | ADMIN       |
| admin2@ksp.local       | Admin2@KSP2025      | ADMIN       |
| manager@ksp.local      | Manager@KSP2025     | MANAGER     |
| manager2@ksp.local     | Manager2@KSP2025    | MANAGER     |
| bendahara@ksp.local    | Bendahara@KSP2025   | BENDAHARA   |
| bendahara2@ksp.local   | Bendahara2@KSP2025  | BENDAHARA   |
| petugas@ksp.local      | Petugas@KSP2025     | PETUGAS     |
| petugas2@ksp.local     | Petugas2@KSP2025    | PETUGAS     |
| petugas3@ksp.local     | Petugas3@KSP2025    | PETUGAS     |

> Registrasi publik dinonaktifkan. Akun baru hanya bisa dibuat oleh SUPER_ADMIN atau ADMIN (ADMIN hanya bisa membuat PETUGAS).

### Demo Data (DemoTransactionSeeder)

Menyiapkan 6 bulan data realistis (Sep 2025 – Feb 2026):

- **15 anggota aktif** (KSP-001 s/d KSP-015)
- **Modal awal:** Rp 300 juta (Kas Tunai) + Rp 200 juta (Bank BRI)
- **Simpanan Wajib:** Rp 100.000/bulan × 15 anggota × 6 bulan
- **Simpanan Sukarela:** 8 anggota dengan jumlah bervariasi tiap bulan
- **Beban operasional:** gaji, biaya bank, listrik, ATK — tiap bulan
- **10 pengajuan pinjaman:** 8 disetujui dengan berbagai tingkat cicilan, 2 masih PENDING

### Run migrations

```bash
php artisan migrate
```

All migrations are applied in order. Key groups:
- Core tables: users, members, cash_accounts, saving_products, loan_products
- Transaction tables: cash_transactions, saving_transactions, loan_applications, loans, loan_payments, loan_application_files
- Accounting tables: accounts, journal_entries, journal_lines
- Extension migrations: penalty rates, notaris fields, form data snapshots, COA ledger links, cash categories, ledger uniqueness constraint, disbursement_fees JSON column

---

## Utility

### `terbilang($amount)`
Helper function in `app/helpers.php`. Converts a numeric amount to Indonesian words (e.g., `terbilang(1500000)` → `"satu juta lima ratus ribu"`). Used in kwitansi PDFs and settlement documents.
