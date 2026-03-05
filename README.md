# KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam

## Overview

KSP-SMM is a web-based financial management system for a savings & loan cooperative (Koperasi Simpan Pinjam). It handles member management, multi-account cash operations, savings products, loan lifecycle (application → approval → disbursement → payments → closure), penalty calculations, notaris deed tracking, and double-entry accounting with automated journal posting.

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

## Directory Structure

```
ksp/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/               # 25 controllers
│   │   │   ├── Master/          #   CashAccount, CashCategory, Member, SavingProduct, LoanProduct
│   │   │   ├── Reports/         #   Cash, Savings, Loans reports (+ CSV export)
│   │   │   ├── Audit/           #   VoidAuditController (+ CSV export)
│   │   │   ├── Accounting/      #   TrialBalanceController
│   │   │   ├── AdminHomeController
│   │   │   ├── UserController
│   │   │   ├── Cash*Controller  #   Income, Expense, Balance, Transfer, Void
│   │   │   ├── Saving*Controller#   Deposit, Withdraw, Void
│   │   │   └── Loan*Controller  #   Application, Loan, Payment, PaymentVoid
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
├── resources/views/             # 45+ Blade templates
│   └── admin/
│       ├── accounting/          #   trial_balance/
│       ├── audit/               #   void/ (cash, savings, loan_payments)
│       ├── cash/                #   balances, expense, income, transfer, void
│       ├── loans/               #   active, applications (+ PDF), payments (+ receipt PDF)
│       ├── master/              #   cash_accounts, cash_categories, loan_products, members, saving_products
│       ├── reports/             #   cash, loans, savings
│       ├── savings/             #   deposit, withdraw, void
│       └── users/               #   create, edit, index
├── routes/
│   ├── web.php                  # All admin routes (role-gated)
│   └── auth.php                 # Auth routes (public /register disabled)
├── database/
│   ├── migrations/              # 37+ migration files
│   └── seeders/                 # RolesSeeder, SuperAdminSeeder, UsersSeeder, AccountingSeeder, CashCategorySeeder
└── config/                      # 11 config files
```

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
| `Loan`               | Approved/active loans with payment schedule and outstanding balance  |
| `LoanPayment`        | Installment payments with penalty fields and void tracking           |

### Accounting Entities

| Model          | Purpose                                                              |
|----------------|----------------------------------------------------------------------|
| `Account`      | Chart of Accounts — 5-level hierarchy (code, name, type, dc, parent)|
| `JournalEntry` | Accounting journal entries (source-tracked, source_type/source_id)   |
| `JournalLine`  | Individual debit/credit lines (account_id, dc, amount)              |

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

## Authorization

Five roles managed via Spatie Permission:

| Role          | Access Level                                                         |
|---------------|----------------------------------------------------------------------|
| `SUPER_ADMIN` | Full access including user/role management                           |
| `ADMIN`       | Administrative operations; can create MANAGER/BENDAHARA/PETUGAS users|
| `MANAGER`     | Approval, reporting, and master data                                 |
| `BENDAHARA`   | Finance/treasury operations; cannot view sensitive member data       |
| `PETUGAS`     | Day-to-day transaction entry; sees only own loan applications        |

Policy rules:
- `MemberPolicy::viewSensitive` — all roles except BENDAHARA
- `LoanApplicationPolicy::viewSensitiveData` — SUPER_ADMIN/ADMIN/MANAGER always; PETUGAS only if `created_by === auth()->id()`
- `LoanApplicationPolicy::approve` — SUPER_ADMIN/ADMIN/MANAGER
- Gates in `AppServiceProvider`: `view-sensitive-member-data`, `create-user`

Master data modifications require: `SUPER_ADMIN`, `ADMIN`, `BENDAHARA`, or `MANAGER`.
Void operations require: `SUPER_ADMIN`, `BENDAHARA`, or `MANAGER`.

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
2. **Approval** — manager/admin approves → `LoanService::approveAndDisburse()`
   - Creates cash expense (disbursement)
   - Creates `Loan` record with calculated schedule
   - Posts GL journal (DR Piutang, CR Kas)
3. **Payments** — `LoanService::payInstallment()`
   - Auto-computes late penalty (days overdue × daily rate bp)
   - Auto-computes early repayment penalty if applicable
   - Creates cash income
   - Updates loan totals
   - Posts GL journal (DR Kas, CR Piutang/Revenue/Denda)
   - Auto-closes loan when outstanding = 0
4. **Void** — reverses payment + cash tx + journal entries

### Void Audit Trail
All voidable entities track: `is_voided`, `void_reason`, `voided_by`, `voided_at`.
Void operations cascade through linked records (transaction → cash → journal).

## PDF Generation

- **Loan Agreement PDF** — `/loans/applications/{id}/agreement` — generated via DomPDF from `agreement-pdf.blade.php`
- **Payment Receipt PDF** — `/loans/payments/{id}/receipt` — generated via DomPDF from `receipt.blade.php`

## Reporting & Audit

| Report              | Filters                         | Export |
|---------------------|---------------------------------|--------|
| Cash Report         | date range, account, type       | CSV    |
| Savings Report      | date range, product, type       | CSV    |
| Loans Report        | date range, product, status     | CSV    |
| Trial Balance       | date range                      | —      |
| Void Audit — Cash   | date range                      | CSV    |
| Void Audit — Savings| date range                      | CSV    |
| Void Audit — Loans  | date range                      | CSV    |

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
# Create roles
php artisan db:seed --class=RolesSeeder

# Create super admin (update credentials in seeder first)
php artisan db:seed --class=SuperAdminSeeder

# Create test users (all use Password123!)
# admin@ksp.local, manager@ksp.local, bendahara@ksp.local, petugas@ksp.local
php artisan db:seed --class=UsersSeeder

# Seed Chart of Accounts (run after migrations)
php artisan db:seed --class=AccountingSeeder

# Seed default cash categories (run after AccountingSeeder)
php artisan db:seed --class=CashCategorySeeder
```

### Run migrations

```bash
php artisan migrate
```

All migrations are applied in order. Key groups:
- Core tables: users, members, cash_accounts, saving_products, loan_products
- Transaction tables: cash_transactions, saving_transactions, loan_applications, loans, loan_payments, loan_application_files
- Accounting tables: accounts, journal_entries, journal_lines
- Extension migrations: penalty rates, notaris fields, form data snapshots, COA ledger links, cash categories, ledger uniqueness constraint

## Utility

### `terbilang($amount)`
Helper function in `app/helpers.php`. Converts a numeric amount to Indonesian words (e.g., `terbilang(1500000)` → `"satu juta lima ratus ribu"`). Used in PDFs.
