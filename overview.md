# KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam

## Overview

KSP-SMM is a web-based financial management system for a savings & loan cooperative (Koperasi Simpan Pinjam). It handles member management, multi-account cash operations, savings products, loan lifecycle, and double-entry accounting with automated journal posting.

## Tech Stack

| Layer      | Technology                                           |
|------------|------------------------------------------------------|
| Backend    | PHP 8.2+, Laravel 12                                 |
| Database   | PostgreSQL 15+                                       |
| Frontend   | Blade, Tailwind CSS 3, Alpine.js 3                   |
| Bundler    | Vite 7                                               |
| Auth/RBAC  | Laravel Breeze + Spatie Permission 7                  |
| Testing    | Pest 4                                               |

## Directory Structure

```
ksp/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # 24 controllers (cash, savings, loans, reports, accounting)
│   │   ├── Auth/            # 8 authentication controllers (Breeze)
│   │   └── Api/V1/          # 1 API controller (loan applications)
│   ├── Models/              # 14 Eloquent models
│   ├── Services/            # 5 business-logic services
│   │   ├── Accounting/      #   PostingService, AutoPostingService
│   │   ├── Cash/            #   CashTransactionService
│   │   ├── Loans/           #   LoanService
│   │   └── Savings/         #   SavingsService
│   └── View/                # Blade view composers
├── resources/views/         # Blade templates (admin, auth, layouts, components)
├── routes/
│   ├── web.php              # Main routes (admin prefix, role middleware)
│   └── auth.php             # Authentication routes
├── database/migrations/     # 32 migration files
└── config/                  # 11 config files
```

## Data Models

### Core Entities

| Model               | Purpose                                          |
|----------------------|--------------------------------------------------|
| `User`               | System users with Spatie roles                   |
| `Member`             | Cooperative members (member_no, name, phone)     |
| `CashAccount`        | Cash accounts (name, type, ledger mapping)       |
| `SavingProduct`      | Savings product definitions (withdrawable flag)  |
| `LoanProduct`        | Loan products (interest rate, admin fee, tenor)  |

### Transaction Entities

| Model                | Purpose                                          |
|----------------------|--------------------------------------------------|
| `CashTransaction`    | Cash income/expense/transfer with void tracking  |
| `SavingTransaction`  | Member savings deposits/withdrawals              |
| `LoanApplication`    | Loan applications with approval workflow         |
| `LoanApplicationFile`| Document attachments for loan applications       |
| `Loan`               | Approved loans with payment schedule             |
| `LoanPayment`        | Installment payments with void tracking          |

### Accounting Entities

| Model          | Purpose                                              |
|----------------|------------------------------------------------------|
| `Account`      | Chart of accounts (hierarchical, parent/child)       |
| `JournalEntry` | Accounting journal entries (source-tracked)           |
| `JournalLine`  | Individual debit/credit lines per journal entry       |

## Service Architecture

### PostingService
- Creates balanced double-entry journal entries
- Validates debit = credit before persisting
- Validates non-empty lines and valid dc (D/C) values
- Voids journal entries by source type/ID

### AutoPostingService
- Automatically generates GL journal entries for:
  - Cash transactions (INCOME/EXPENSE/TRANSFER)
  - Savings deposits/withdrawals
  - Loan disbursements
  - Loan installment payments
- Skips system categories (handled by respective modules)
- Guards against duplicate posting (excludes voided entries)
- Maps cash accounts to ledger accounts via `ledger_account_id`

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
- Overpayment guard — payment cannot exceed outstanding balance
- Payment voiding cascades: voids payment -> cash tx -> journal entries
- Recomputes loan totals from non-voided payments

### SavingsService
- Processes deposits and withdrawals
- Balance check before withdrawal
- Creates corresponding cash transactions (system category)
- Voiding cascades: voids saving tx -> cash tx -> journal entries

## Authorization

Five roles managed via Spatie Permission:

| Role          | Access Level                                      |
|---------------|---------------------------------------------------|
| `SUPER_ADMIN` | Full access                                      |
| `ADMIN`       | Administrative operations                         |
| `BENDAHARA`   | Finance/treasury operations                       |
| `MANAGER`     | Approval and reporting                            |
| `PETUGAS`     | Day-to-day transaction entry                      |

Master data modifications require: `SUPER_ADMIN`, `ADMIN`, `BENDAHARA`, or `MANAGER`.
Void operations require: `SUPER_ADMIN`, `BENDAHARA`, or `MANAGER`.

## Key Workflows

### Cash Transaction Flow
1. User creates income/expense/transfer via controller
2. Controller validates input -> calls `CashTransactionService`
3. Service creates `CashTransaction` with temp UUID code
4. Updates code to sequential format using record ID
5. `AutoPostingService` creates journal entry (skips system categories)

### Loan Lifecycle
1. **Application** — member submits via `LoanApplication`
2. **Approval** — officer approves -> `LoanService::approveAndDisburse()`
   - Creates cash expense (disbursement)
   - Creates `Loan` record with calculated schedule
   - Posts GL journal (DR Piutang, CR Kas)
3. **Payments** — `LoanService::payInstallment()`
   - Creates cash income
   - Updates loan totals
   - Posts GL journal (DR Kas, CR Piutang/Revenue)
   - Auto-closes loan when outstanding = 0
4. **Void** — reverses payment + cash tx + journal entries

### Void Audit Trail
All voidable entities track: `is_voided`, `void_reason`, `voided_by`, `voided_at`.
Void operations cascade through linked records (transaction -> cash -> journal).

## Reporting

- Cash reports with export (income, expense, transfer)
- Savings reports with export (deposits, withdrawals)
- Loan reports with export (active, payments, applications)
- Trial balance (double-entry verification with date range)
- Void audit trails for cash, savings, and loan payments

## Development

```bash
# Full setup
composer run setup          # composer install, npm install, migrate, build

# Development server
composer run dev            # concurrent: artisan serve + queue + npm dev

# Testing
composer run test           # Pest test suite
```

**Environment:** PostgreSQL on `localhost:5432`, database `ksp_smm`, app URL `http://localhost:8000`
