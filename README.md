# KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam

## Overview

KSP-SMM is a web-based financial management system for a savings & loan cooperative (Koperasi Simpan Pinjam). It handles member management, multi-account cash operations, savings products, loan lifecycle (application → approval → disbursement → payments → closure), penalty calculations, notaris deed tracking, disbursement receipts (kwitansi), double-entry accounting with automated journal posting, and a full collection/penagihan module with escalation tracking.

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
| 23 | Role matrix refactor — initial role-based access control with per-route middleware | ✅ Done |
| 24 | PK B (Kendaraan) loan agreement — vehicle collateral PDF template (Pasal 3 with 11 vehicle spec fields), separate from PK A (Sertipikat) | ✅ Done |
| 25 | Loan show page UI — "Cetak Dokumen" dropdown consolidates kwitansi + PK + permohonan dana; Bayar Angsuran as primary action | ✅ Done |
| 26 | New roles — ADMIN_DATA, ADMIN_COLLECTION with full route/sidebar/policy integration | ✅ Done |
| 27 | Subledger PDF denda columns — Hari + Jumlah sub-columns between "Jumlah Angsuran" and "Pembayaran" | ✅ Done |
| 28 | Settlement PDF angsuran tertunggak — overdue installment breakdown section with per-installment detail and days-late | ✅ Done |
| 29 | Early repayment rate 5% — updated DemoMasterSeeder to `updateOrCreate` so rates apply to existing DB records | ✅ Done |
| 30 | PK PDF pasal page-break fix — `page-break-inside: avoid` on `.pasal-block` prevents headers splitting from body across pages (PK A + PK B) | ✅ Done |
| 31 | **Collection module** — Master Tagihan Nasabah, Data Master DHIA, Laporan Collection (collectability buckets), 5 collection letter PDFs (SP I–III, Somasi, Pra-Cessie), per-loan collection records with escalation tracking | ✅ Done |
| 32 | **COA Management UI** — CRUD for Chart of Accounts (add/edit/deactivate accounts), restricted to SUPER_ADMIN | ✅ Done |
| 33 | **Member Savings Balance View** — per-member savings summary across all products, printable passbook-style | ✅ Done |
| 34 | **Loan Disbursement Posting Fix** — `disbursement_fees` JSON deductions posted as a supplementary journal entry (DR Kas, CR income accounts per fee type) via `postLoanDisbursementFees()` | ✅ Done |
| 35 | **Notification System** — in-app computed alerts in sidebar badge + notifications page; role-filtered: pending approvals, overdue loans 30d, near-maturity, draft SHU | ✅ Done |
| 36 | **Member Portal** — MEMBER role + `user_id` link on members; read-only self-service at `/portal` (dashboard, simpanan history, pinjaman & payments); admin links user ↔ member in user management | ✅ Done |
| 37 | **Collateral Registry** — `loan_collaterals` table per-application; CRUD (type, description, estimated value, document upload); HELD/RELEASED workflow with release note; shown inline on application show page | ✅ Done |
| 38 | **Bulk Simpanan Wajib** — batch monthly savings entry form: select product + cash account + date, enter amounts per active member with Alpine.js running total; one saving transaction per member in a single DB transaction | ✅ Done |
| 39 | **Loan Application Agreement v2** — PK A and PK B PDFs incorporate notaris deed data (number, date, name, city) and disbursement fees table (biaya pencairan breakdown) | ✅ Done |
| 40 | **Print Statements — Cash Balances** — "Cetak PDF" print button on Saldo Kas page with `window.print()`, print-hidden sidebar, and timestamp watermark | ✅ Done |
| 43 | **Audit Log** — `audit_logs` table + `AuditLogService`; records void (cash/savings/loan payment) and loan approval/rejection events; browse UI with filter by action/module/user/date + CSV export; sidebar "Log Aktivitas" link; Audit VOID submenus upgraded (project-standard UI, print button, date formatting, type badges, stats banner); void forms upgraded (card layout, textarea reason, JS confirm dialog); removed redundant `abort_unless` MANAGER check from all 3 void controllers | ✅ Done |
| 44 | **Collection Module Restructure** — Pelunasan, Jadwal Angsuran (Amortisasi), and SubLedger moved from Pinjaman Approved into Admin Collection; new collection-prefixed routes (`/collection/{loan}/schedule`, `/settlement`, `/subledger/pdf`); sidebar "Amortisasi" and "Sub Ledger" dead links removed; "Master Tagihan" sidebar item stays highlighted when on schedule/settlement/subledger pages; views detect context (`routeIs('admin.collection.*')`) for correct breadcrumbs and back-button targets; **Progress Bayar** (color-coded progress bar + %) and **Sisa Tagihan** (outstanding Rp) columns added to Master Tagihan Nasabah table | ✅ Done |
| 45 | **Role Matrix Revision + Legal Module** — 7-role system (SUPER_ADMIN, FINANCE, ADMIN_DATA, ADMIN_COLLECTION, TREASURY, PENGAWAS, LEGAL); LEGAL role with dedicated Legal Document section (LegalBerkas + LegalDocument models, multi-file upload with Alpine.js); Master Data split (Anggota = broader access, Financial = FINANCE only); button visibility gating across all views; Treasury removed from Pinjaman Approved (kwitansi/schedule accessible via Pengajuan show page) | ✅ Done |
| 46 | **Card-Based Navigation System** — sidebar removed, replaced with mobile-app-style department card menu on dashboard; 5 department cards (Admin Data, Admin Collection, Treasury, Legal, Finance) + Sistem card (SUPER_ADMIN only); Alpine.js drill-down transitions; role-based card visibility; per-department sub-menu items without cross-department duplication; auto-generated breadcrumbs (`Home > Section > Page`) from route name mapping; persistent Home button in navbar; color-coded role badge in navbar; notification badge integrated into welcome banner; KPI summary (Anggota, Saldo Kas, Simpanan, Piutang) for FINANCE/SUPER_ADMIN below menu cards | ✅ Done |
| 47 | **Training Guides & Production Setup** — printable per-role training guides (7 roles) at `/admin/guide`; `ProductionSeeder` (roles + COA + cash categories only, no demo data); `ksp:create-superadmin` artisan command for bootstrapping first account; "Panduan" button on dashboard | ✅ Done |
| 48 | **Cash & Savings Voucher PDFs** — DomPDF voucher templates for individual cash and savings transactions; `CashTransactionController::voucher()` (Receipt/Payment/Transfer Voucher) and `SavingTransactionController::voucher()`; A4 portrait, company header, ref/code/amount block, line items table; routes `/cash/transactions/{tx}/voucher` and `/savings/transactions/{tx}/voucher` | ✅ Done |
| 49 | **Loan Application Review PDF** — printable application review document (`LoanApplicationController::reviewPdf()`); route `/loans/applications/{application}/review-pdf`; ADMIN_DATA scoped to own records, SUPER_ADMIN/TREASURY/PENGAWAS see all (LEGAL excluded) | ✅ Done |
| 50 | **Collateral Edit** — `LoanCollateralController::edit()`/`update()` and `admin/collaterals/edit.blade.php`; replace document file (deletes old), update type/description/value while status = HELD; RELEASED collaterals are immutable; "Edit" button added to collateral index table | ✅ Done |
| 51 | **Member City Field** — new nullable `city` column on `members` table (migration `2026_04_08_110000_add_city_to_members_table`); added to `Member::$fillable`, MemberController validation, and create/edit forms | ✅ Done |
| 52 | **Drop Admin Fee from Loan Tables** — migration `2026_04_08_100000_drop_admin_fee_from_loan_tables` removes `admin_fee` column from `loan_products` and `loan_applications`; admin fees are now sourced exclusively from the `disbursement_fees` JSON on the loan record (cleaner single source of truth). `admin_income_account_id` GL link is preserved for posting | ✅ Done |
| 53 | **Collection Denda Column** — Master DHIA table now displays per-loan late penalty (`_denda` = outstanding × `penalty_daily_rate_bp` × `_days_overdue`) and Days Overdue, computed in `CollectionController::enrichLoan()` from the first unpaid installment date | ✅ Done |
| 54 | **Alamat KTP Fields** — separate KTP address section on loan application create/edit forms (alamat_ktp, rt_ktp, rw_ktp, kelurahan_ktp, kecamatan_ktp, kota_ktp, kode_pos_ktp); stored in `form_data` JSON; kelurahan + kecamatan also added to domicile address section | ✅ Done |
| 55 | **Pinjaman Efektif (Decreasing Interest)** — EFEKTIF interest method alongside FLAT; bunga dihitung dari sisa pokok; `LoanService::approveAndDisburse()` computes declining schedule; `method` column on `loans` table; loan product dropdown auto-selects method; ANNUITY→EFEKTIF data migration; DemoMasterSeeder adds "Pinjaman Efektif" product (3%/month) | ✅ Done |
| 56 | **Finance Review Workflow** — new status `FINANCE_REVIEWED` between PENDING→APPROVED; FINANCE role sets interest rate + method before SUPER_ADMIN approves; `financeReview()` action on LoanApplicationController; Finance can view all loan applications (read-only list + detail); blue badge for FINANCE_REVIEWED status | ✅ Done |
| 57 | **Collection Letter Address Enhancement** — SP1–SP3, Somasi, and Cessie PDF letters now display full alamat tinggal (from `form_data` fields: alamat, rt, rw, kelurahan, kecamatan, kota, kode_pos) and alamat jaminan (property collateral address with status agunan) instead of short `member->address` | ✅ Done |
| 58 | **PK Word Export** — download PK A (Sertipikat) and PK B (Kendaraan) agreement documents as `.docx` Word files; CSS-to-inline-styles conversion for PhpWord compatibility; HTML entity/XHTML sanitization; float-to-inline conversion for numbered layouts; buttons on application show page | ⚠️ Partial — formatting not fully matching PDF (PhpWord HTML parser limitations) |
| 59 | **Kwitansi Biaya-Biaya** — standalone manual fee receipt for active loans; Alpine.js dynamic form (add/remove rows with description + amount); printable kwitansi with KSP letterhead, fee table, total, and dual-signature block; route from active loan show page dropdown | ✅ Done |
| 60 | **Denda System Overhaul** — full penalty lifecycle fix: (1) reactive Alpine.js recalculation of accumulated late-payment penalty as `trxDate` changes; (2) `calcAccumulatedPenalty()` now iterates all installment slots so paid-late slots without recorded denda are included in carry-forward; (3) denda keterlambatan allowed on regular payments (not just pelunasan dipercepat); (4) late-payment flag (`is_late`) added to schedule builder — orange row + "Terlambat" badge in jadwal table; (5) `outstanding <= 10 → CLOSED` safety net absorbs intdiv rounding remainder (Sisa Rp 5 fix); (6) per-installment penalty breakdown (slot # × amount × days × 0.3%) shown in payment form; (7) green "Total Denda Sudah Dibayar" banner card; (8) AutoPostingService stale comment cleanup (admin references removed) | ✅ Done |
| 61 | **Permohonan Dana Enhancements** — (1) document redesigned to match Word template: two-column loan detail grid (Jenis Fasilitas, Produk, Objek, No. Anggota, Jangka Waktu, Angsuran/Bulan, Suku Bunga, Bunga Total, Nilai Pembiayaan, Pencairan Bersih); (2) Pencairan Bersih section expanded to show "Jumlah potongan" + "Jumlah di setor" rows in both web view and PDF; (3) Hold Angsuran replaced with two-field Alpine.js component: "Angsuran ke-" number input auto-calculates Hold Angsuran = `ke × monthly_installment`; (4) biaya display annotates Hold Angsuran with "(X angsuran)" in both web view and PDF cetak | ✅ Done |
| 62 | **Deposito Cash Account Type + Finance Hardening** — (1) New `DEPOSITO` type added to `cash_accounts` (CASH/BANK/CHECK/GIRO/DEPOSITO); auto-creates leaf ledger under COA 1160 "Penempatan Deposito"; excluded from Total Saldo Kas on dashboard and Saldo Kas page — shown in separate amber "Tidak dihitung ke Total Saldo" section; (2) Bayar Angsuran role changed from `ADMIN_COLLECTION` → `FINANCE`; (3) Kode Referensi input field added to Setoran & Penarikan Simpanan forms; (4) Transfer Kas menu removed from Finance sidebar; (5) "Jenis Transaksi" dropdown (Tunai/Transfer/Cek/Giro) added to Pemasukan & Pengeluaran Kas forms — new `payment_method` VARCHAR column on `cash_transactions`; (6) Print PDF button added to Deposito index (landscape list) and show (A4 bilyet/certificate); (7) Edit button + form for Deposito (editable: rekening bank pencairan only; pokok/bunga/tenor/tanggal locked); (8) Trial balance/balance sheet/income statement imbalance fixed — removed `accounts.is_active = true` filter so legacy deactivated COA accounts with existing `journal_lines` are included, restoring D=C double-entry balance | ✅ Done |
| 63 | **Critical Audit + Day-1 Hotfixes** — comprehensive static audit of money-path modules (loan service, cash service, savings service, accounting auto-posting, policies); 14 bugs identified, 12 fixed: Pelunasan Pinjaman double-journal-posting fix (added to `systemCats` allowlist in `AutoPostingService`); `LoanCollateralPolicy` created (button hidden for SUPER_ADMIN due to missing policy); audit log `$application->amount` corrected to `$application->principal` (was logging `0`) | ✅ Done |
| 64 | **Post-Audit Hardening (Day-2/3)** — EFEKTIF method stopgap (UI disabled + server validation rejects until per-installment math is properly wired); early repayment must equal outstanding (validation in `LoanService::payInstallment`); `hold_angsuran_ke` capped at tenor months; `CashAccount::lockForUpdate()` on expense/transfer balance check (prevents race condition on concurrent outflows); fee journal voided + re-posted on edit (prevents ledger drift in `saveKwitansiPencairan`); settlement view dead code removed + tunggakan labeled "informational"; open-redirect guard hardened with `parse_url` host match; N+1 fix on collection laporan (loanProduct eager-loaded); MemberPolicy stale comment cleanup | ✅ Done |
| 65 | **Finance Role Access Expansion** — FINANCE added to Pinjaman Aktif index/show + schedule + settlement + subledger routes; new "Pinjaman" section in Finance home submenu (Pinjaman Aktif + Pengajuan Pinjaman); Cash Category dropdown filter expanded to include LIABILITY + ASSET account types (was only REVENUE+EQUITY for income, EXPENSE for expense — now allows deposit→liability mapping etc.); FINANCE granted COA view + create + edit access (toggle remains SUPER_ADMIN only) | ✅ Done |
| 66 | **Neraca Data Correction (Production)** — diagnosed Rp 5.683B balance sheet imbalance via SQL forensic queries; root cause: 13 loan disbursements + 6 loan payments referenced wrong `receivable_account_id` (account 16 "Kas" parent — invisible due to `whereDoesntHave('children')` filter) instead of account 18 "Piutang Pinjaman" leaf; fixed via two `UPDATE journal_lines SET account_id = 18` SQL operations; account 16 emptied, neraca balanced (D=C maintained system-wide throughout) | ✅ Done |
| 67 | **Missing COA Accounts + Deposito Label Migration** — 7 missing accounts added via `add_missing_coa_accounts` migration: 5117 Beban Notaris, 5118 Beban Asuransi, 5119 Beban Pajak, 5216 Beban Tolakan Transaksi Keluar, 5217 Beban Admin Transfer/RTGS, 4213 Pendapatan Transfer/RTGS Masuk, 4214 Pendapatan Retur/Tolakan Masuk; deposito ledger (1111) renamed to "Deposito Berjangka — Dana Anggota Dikelola" per client option-A (standard double-entry preserved, label clarifies member-managed funds vs operational asset) | ✅ Done |
| 68 | **Non-Anggota Depositor** — `deposito_accounts.member_id` made nullable; new `depositor_name`, `depositor_phone`, `depositor_ktp` columns; `saving_transactions.member_id` also nullable to allow flow-through; create form has Anggota/Non-Anggota toggle (Alpine.js); show + index + print views handle null member with `depositorDisplayName()` helper; same ledger account 2113 used (reports stay combined per client requirement — this is operational tracking only, not separate accounting) | ✅ Done |
| 69 | **Active Loan Search + Advance Account + Petugas Rename** — search bar on Pinjaman Aktif index (filter by member name or member_no via `ilike`); new account 2124 "Advance — Angsuran Tidak Teridentifikasi" (LIABILITY) for unidentified payments + matching cash category; users `dilla@ksp.local` and `nadia@ksp.local` renamed to "Dilla / Nadiah" so kwitansi shows team name regardless of which kasir logged in | ✅ Done |
| 70 | **Treasury Role Document Fixes** — (1) PK Word export replaced with native PhpWord builder (`PkWordService`) — full 13-pasal legal document built programmatically, no more HTML→PhpWord fragile conversion; (2) KSP kop surat (logo via base64) added to PK A, PK B, Permohonan Dana PDF; OL black-box logo fixed (JPEG base64 instead of `public_path`); (3) Kwitansi kop surat enlarged across all 4 kwitansi (logo `w-14→w-20`, title `text-xl→text-2xl`, address line added); (4) Biaya 2-rupiah selisih fixed — `hold_angsuran_ke` integer count was being summed with rupiah amounts; excluded via `collect()->except()` in both web view and PDF; (5) Treasury can now see kwitansi angsuran — added `TREASURY` to `admin.loans.active.index` + `show` routes + added "Pinjaman Aktif" entry to Treasury home submenu + "Kwitansi" button per payment row visible to FINANCE+TREASURY; (6) Jadwal Angsuran PDF redesigned from amortisasi table to simple client-requested format: kop surat + header info (No. Anggota, Nama, Alamat, No. Rekening, Jangka Waktu, Plafond, Realisasi, Jatuh Tempo) + simple table (Angsuran ke / Tanggal / Jumlah) + signature block; (7) Permohonan Dana web view kop surat added (logo + full address) | ✅ Done |
| 71 | **Admin Collection Corrections** — (1) `CollectionOverdueSeeder`: new seeder to simulate varying overdue (1–6 bulan tunggak) on active loans for demo/testing without touching loan_payments history; (2) SP letter suggestion now auto-selects type based on `$monthsLate` (not manual escalation_level): 1 bln→SP1, 2–3 bln→SP2, 4 bln→SP3, 5–6 bln→Somasi, 7+ bln→Cessie; (3) Collection show page expanded: full payment history (all rows, not just 10), Kwitansi + Void action buttons per row, + new Rincian Pinjaman card (komponen angsuran, rekapitulasi, tunggakan); (4) Separate **Bayar Angsuran** (indigo) and **Perhitungan Pelunasan** (amber) buttons in collection show header; (5) **Settlement fix** — `totalPelunasan` for early repayment now uses `sisaPokok + bungaTertunggak` (past-due interest only) instead of `outstanding` (which incorrectly included all future interest); `sisa_pokok` now matches `outstanding_principal` column in amortization schedule exactly; future interest is explicitly shown as waived in both web view and PDF | ✅ Done |

---

## Planned Milestones

### Features

| # | Milestone | Priority |
|---|-----------|----------|
| 48 | **Real-Time Collection Updates (Laravel Echo)** — broadcast `LoanPayment` created/voided events via WebSocket (Laravel Echo + Pusher/Soketi); Master Tagihan Nasabah collection index subscribes to payment channel and updates Progress Bayar and Sisa Tagihan cells live without page refresh | **High** |
| 48 | **Restrukturisasi Pinjaman** — restructure active loans: extend tenor, reduce rate, reschedule installments; tracks original vs restructured terms; GL adjustment journal | High |
| 49 | **API v1 Expansion** — REST endpoints for loan applications (currently partial), member data, payments; token auth via Laravel Sanctum | Medium |
| 50 | **Rekening Koran (Account Statement)** — monthly per-member consolidated statement (simpanan + pinjaman) with PDF download and optional email delivery | Medium |
| 51 | **Multi-Branch Support** — `branches` table, each member/cash account/loan scoped to a branch; branch-level reporting and dashboards; cross-branch transfer workflow | Medium |
| 52 | **Approval Workflow Engine** — configurable multi-level approval chains for loans; approval history log with comments | Medium |
| 53 | **SMS / WhatsApp Notifications** — automated reminders for upcoming installments (H-3), overdue alerts, and approval status changes via Fonnte/Wablas gateway | Medium |
| 54 | **Simpanan Berjangka (Deposito)** — time-deposit savings product with fixed tenor, maturity date, auto-rollover option, break-penalty, and interest accrual journal | Low |
| 55 | **Member Document Vault** — centralized per-member file storage (KTP, KK, slip gaji, etc.) with categorization, expiry tracking, and access control | Low |
| 56 | **Dashboard Analytics v2** — interactive charts (Chart.js / Livewire): monthly trend lines for disbursements, collections, NPL ratio, savings growth; drill-down by product/branch | Low |
| 57 | **Bulk Loan Payment Import** — CSV/Excel upload for batch angsuran processing (bank auto-debit reconciliation); validation preview before commit | Medium |

### Hardening

| # | Milestone | Priority |
|---|-----------|----------|
| 58 | **2FA / OTP Login** — optional TOTP second factor for SUPER_ADMIN and ADMIN accounts using `pragmarx/google2fa-laravel` | High |
| 59 | **Automated Test Coverage** — Pest feature tests for critical workflows (loan lifecycle, cash void, SHU finalization, AutoPostingService balance integrity) | High |
| 60 | **Input Validation Hardening** — centralized Form Request classes for all store/update actions; replace inline `$request->validate()` with dedicated FormRequest objects | High |
| 61 | **Database Backup & Restore** — scheduled `pg_dump` via Laravel scheduler (daily/weekly); admin UI to list backups, download, and one-click restore; retention policy | High |
| 62 | **Comprehensive Logging & Monitoring** — extend AuditLog to cover login/logout, data exports, master data changes; structured JSON logs for external SIEM integration | Medium |
| 63 | **Rate Limiting & Brute Force Protection** — login throttle (5 attempts/15 min lockout), API rate limiting per token, CAPTCHA on repeated failures | Medium |
| 64 | **Data Encryption at Rest** — encrypt sensitive member fields (NIK, phone, address) using Laravel's `encrypted` cast; key rotation strategy | Low |
| 65 | **Content Security Policy (CSP)** — strict CSP headers via middleware; nonce-based inline script allowlisting; Subresource Integrity (SRI) for CDN assets | Low |

### Scaling & Infrastructure

| # | Milestone | Priority |
|---|-----------|----------|
| 66 | **Queue-Based Heavy Operations** — move PDF generation, bulk simpanan wajib, CSV exports, and SHU calculation to Laravel queues (Redis/database driver); progress bar UI | High |
| 67 | **Database Performance Optimization** — add composite indexes on high-query tables (journal_lines, loan_payments, cash_transactions by date/account); query caching for dashboard KPIs; N+1 query audit | High |
| 68 | **Dockerized Deployment** — `docker-compose.yml` with PHP-FPM, Nginx, PostgreSQL, Redis containers; `.env.docker` template; CI/CD pipeline (GitHub Actions) for build → test → deploy | Medium |
| 69 | **Read Replica & Connection Pooling** — Laravel database read/write splitting config; PgBouncer for connection pooling; session-based sticky reads for write-after-read consistency | Low |
| 70 | **Asset & Response Caching** — Vite asset versioning, HTTP cache headers for static files; server-side response caching for report pages (5-min TTL); Redis cache driver | Medium |
| 71 | **Horizontal Scaling Readiness** — session storage in Redis (replace file driver), centralized file storage (S3-compatible for uploads/documents), stateless app servers behind load balancer | Low |

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
| Word       | phpoffice/phpword 1.x (PK agreement .docx export)    |
| Testing    | Pest 4                                               |
| Real-time  | Laravel Echo + WebSocket broadcasting *(planned M45)* |

---

## Directory Structure

```
ksp/
├── app/
│   ├── Console/Commands/        # CreateSuperAdmin (ksp:create-superadmin)
│   ├── Http/Controllers/
│   │   ├── Admin/               # 35 controllers
│   │   │   ├── Master/          #   CashAccount, CashCategory, Member, SavingProduct, LoanProduct
│   │   │   ├── Reports/         #   Cash, Savings, Loans reports (+ CSV export)
│   │   │   ├── Audit/           #   VoidAuditController, AuditLogController (+ CSV export)
│   │   │   ├── Accounting/      #   GeneralLedger, JournalEntry, IncomeStatement, BalanceSheet, SHU
│   │   │   ├── Collection/      #   CollectionController, CollectionLetterController
│   │   │   ├── LegalController         # Legal document bundles (berkas) CRUD + file management
│   │   │   ├── TrainingGuideController # Role-based printable training guides
│   │   │   ├── AdminHomeController
│   │   │   ├── UserController
│   │   │   ├── NotificationController  # computed in-app alerts
│   │   │   ├── LoanCollateralController# collateral CRUD + release workflow
│   │   │   ├── BulkSavingController    # batch mandatory savings entry
│   │   │   ├── Cash*Controller  #   Income, Expense, Balance, Transfer, Void
│   │   │   ├── Saving*Controller#   Deposit, Withdraw, Void
│   │   │   └── Loan*Controller  #   Application, Loan (+ settlement/kwitansi/permohonan/schedule/subledger), Payment, PaymentVoid
│   │   ├── Portal/              # MemberPortalController (dashboard, savings, loans)
│   │   ├── Auth/                # 8 Breeze authentication controllers
│   │   └── Api/V1/              # 1 API controller (loan applications)
│   ├── Models/                  # 18 Eloquent models
│   ├── Services/                # 5 business-logic services
│   │   ├── Accounting/          #   PostingService, AutoPostingService
│   │   ├── Cash/                #   CashTransactionService
│   │   ├── Loans/               #   LoanService
│   │   └── Savings/             #   SavingsService
│   ├── Policies/                # MemberPolicy, LoanApplicationPolicy
│   └── helpers.php              # terbilang() — number to Indonesian words
├── resources/views/             # 60+ Blade templates
│   └── admin/
│       ├── accounting/          #   trial_balance/, general_ledger/, journal_entries/, income_statement/, balance_sheet/, shu/
│       ├── audit/               #   log/ (AuditLog), void/ (cash, savings, loan_payments)
│       ├── cash/                #   balances, expense, income, transfer, void (each with print-all PDF view)
│       ├── collection/          #   index (Master Tagihan), show, laporan, master-dhia, letters/ (5 PDF templates)
│       ├── loans/               #   active/ (show, schedule*, settlement*, subledger PDF*, kwitansi-plafon, kwitansi-pencairan, permohonan-dana)
│       │                        #   applications/ (index, show, create, edit, edit-personal, agreement PDF, agreement-vehicle PDF, penawaran PDF)
│       │                        #   payments/ (create, receipt PDF)
│       │                        #   * also accessible via collection-prefixed routes
│       ├── guide/               #   index + 7 role-specific printable training guides
│       ├── legal/               #   index, create, show (document bundles)
│       ├── collaterals/         #   index, create, release
│       ├── master/              #   cash_accounts, cash_categories, loan_products, members, saving_products
│       ├── notifications/       #   index (computed alerts)
│       ├── password/            #   change
│       ├── reports/             #   cash, loans, savings
│       ├── savings/             #   deposit, withdraw, void, bulk_wajib/
│       ├── users/               #   create, edit, index
│       └── portal/              #   dashboard, savings, loans (Member Portal)
├── routes/
│   ├── web.php                  # All admin routes (role-gated per finance matrix) + portal routes
│   └── auth.php                 # Auth routes (public /register disabled)
├── database/
│   ├── migrations/              # 44+ migration files
│   └── seeders/                 # RolesSeeder (8 roles incl. MEMBER), UsersSeeder (10 team accounts), DemoMasterSeeder, AccountingSeeder, CashCategorySeeder, DemoTransactionSeeder, MasterConfigSeeder, ProductionSeeder
└── config/                      # 11 config files
```

---

## Data Models

### Core Entities

| Model              | Purpose                                                             |
|--------------------|---------------------------------------------------------------------|
| `User`             | System users with Spatie roles                                      |
| `Member`           | Cooperative members (member_no, name, phone, NIK, address, city, active, user_id for portal) |
| `CashAccount`      | Cash accounts (name, type CASH/BANK/CHECK/GIRO/DEPOSITO, ledger auto-linked, active) |
| `CashCategory`     | Cash transaction categories (type INCOME/EXPENSE/TRANSFER, linked GL account) |
| `SavingProduct`    | Savings product definitions (interest rate, withdrawable flag)      |
| `LoanProduct`      | Loan products (interest rate, admin fee, tenor, penalty rates in bp, linked GL accounts) |
| `CollectionRecord` | One-per-loan collection tracking: petugas, branch, escalation level (0–5), contact dates, is_restruk, property data (NJOP, LT, LB, alamat_tinggal, wilayah) |
| `CollectionLetter` | Per-loan collection letters issued: type (SP1/SP2/SP3/SOMASI/CESSIE), letter number, date, creator |
| `LegalBerkas`    | Legal document bundles — title, description, optional member/loan_application link, created_by |
| `LegalDocument`  | Individual files within a berkas — original_name, file_path, file_size, mime_type |

### Transaction Entities

| Model                | Purpose                                                              |
|----------------------|----------------------------------------------------------------------|
| `CashTransaction`    | Cash income/expense/transfer with category, `payment_method` (TUNAI/TRANSFER/CEK/GIRO), and void tracking |
| `SavingTransaction`  | Member savings deposits/withdrawals with void tracking               |
| `LoanApplication`    | Loan applications with approval workflow, form data snapshot, notaris details, file attachments |
| `LoanApplicationFile`| Document attachments for loan applications (type, path, filename)   |
| `Loan`               | Approved/active loans with payment schedule, outstanding balance, `disbursement_fees` JSON, and `method` (FLAT/EFEKTIF) |
| `LoanPayment`        | Installment payments with penalty fields and void tracking           |
| `LoanCollateral`     | Collateral assets per loan application: type, value, document, HELD/RELEASED status |

### Accounting Entities

| Model          | Purpose                                                              |
|----------------|----------------------------------------------------------------------|
| `Account`      | Chart of Accounts — 5-level hierarchy (code, name, type, dc, parent)|
| `JournalEntry` | Accounting journal entries (source-tracked, source_type/source_id)   |
| `JournalLine`  | Individual debit/credit lines (account_id, dc, amount)              |
| `AuditLog`     | System activity log: user, action, subject_type/id, description, metadata (jsonb) |
| `ShuPeriod`    | SHU calculation period with allocation percentages                   |
| `ShuMemberAllocation` | Per-member SHU distribution for a given period                 |

---

## Chart of Accounts (COA)

Seeded by `AccountingSeeder`. Five-level hierarchy:

| Code Range | Category                        | Normal Balance |
|------------|---------------------------------|----------------|
| 1xxx       | Assets (Aktiva)                 | Debit          |
| 1110       | Kas (Cash accounts group)       | Debit          |
| 1120       | Bank accounts group             | Debit          |
| 1140       | Cek (Check) accounts group      | Debit          |
| 1150       | Giro accounts group             | Debit          |
| 1160       | Penempatan Deposito group       | Debit          |
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
- `CashAccount` (CASH type) → leaf auto-generated under 1110 (111x); (BANK type) → leaf under 1120 (112x); (CHECK) → 114x; (GIRO) → 115x; (DEPOSITO) → 116x — excluded from Total Saldo Kas
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

### Role Matrix (Active Roles)

| Role               | Responsibility |
|--------------------|----------------|
| `SUPER_ADMIN`      | Full system access including user/role management, accounting, and legal documents |
| `FINANCE`          | Accounting & finance — kas, simpanan, laporan, master data (all), audit, akuntansi |
| `ADMIN_DATA`       | Loan administration — create/manage loan applications, master anggota (rw), pinjaman approved (view) |
| `ADMIN_COLLECTION` | Collection — loan payment entry + collection module (Master Tagihan, Amortisasi, Pelunasan, SubLedger, DHIA), master anggota (ro) |
| `TREASURY`         | Document management — PK, Penawaran Pembiayaan, Kwitansi (Plafon/Pencairan), Permohonan Dana, Jadwal Angsuran; pengajuan (view), DHIA (view only) |
| `PENGAWAS`         | Supervisor (read-only) — view pengajuan, pinjaman approved, all collection menus, master anggota |
| `LEGAL`            | Same access as PENGAWAS + dedicated Legal Document section (upload/manage berkas with multiple files) |
| `MEMBER`           | Member self-service — access to read-only Member Portal only (no admin area) |

Legacy roles (unused, kept for backward compatibility): ADMIN, MANAGER, BENDAHARA, PETUGAS, KASIR

### Access Matrix

| Module / Action | SUPER_ADMIN | FINANCE | ADMIN_DATA | ADMIN_COLL | TREASURY | PENGAWAS | LEGAL |
|-----------------|:-----------:|:-------:|:----------:|:----------:|:--------:|:--------:|:-----:|
| **Kas** — input/lihat/void | ✓ | ✓ | — | — | — | — | — |
| **Simpanan** — setor/tarik/void | ✓ | ✓ | — | — | — | — | — |
| **Simpanan** — saldo anggota | ✓ | ✓ | — | — | — | — | — |
| **Pengajuan** — buat | ✓ | — | ✓ | — | — | — | — |
| **Pengajuan** — lihat | ✓ | — | ✓ | — | ✓ ro | ✓ ro | ✓ ro |
| **Pengajuan** — edit (PENDING) | ✓ | — | ✓ | — | — | — | — |
| **Pengajuan** — cetak dokumen (PK, OL, Kwitansi) | ✓ | — | — | — | ✓ | — | — |
| **Pengajuan** — setujui / tolak | ✓ | — | — | — | — | — | — |
| **Pengajuan** — kelola agunan | ✓ | — | ✓ | — | — | — | — |
| **Pinjaman Approved** — lihat | ✓ | — | ✓ ro | ✓ ro | — | ✓ ro | ✓ ro |
| **Pinjaman Approved** — bayar angsuran | ✓ | ✓ | — | — | — | — | — |
| **Pinjaman Approved** — cetak dokumen | ✓ | — | — | — | ✓ | — | — |
| **Penagihan** — lihat (index, show, laporan) | ✓ | — | — | ✓ | — | ✓ | ✓ |
| **Penagihan** — DHIA | ✓ | — | — | ✓ | ✓ ro | ✓ | ✓ |
| **Penagihan** — Pelunasan | ✓ | — | — | ✓ | — | — | — |
| **Penagihan** — Amortisasi / SubLedger | ✓ | — | — | ✓ | — | ✓ ro | ✓ ro |
| **Laporan** (kas, simpanan, pinjaman) | ✓ | ✓ | — | — | — | — | — |
| **Akuntansi / Audit** | ✓ | ✓ | — | — | — | — | — |
| **Master Data — Anggota** — lihat | ✓ | ✓ | ✓ | ✓ ro | — | ✓ ro | ✓ ro |
| **Master Data — Anggota** — kelola | ✓ | ✓ | ✓ | — | — | — | — |
| **Master Data — Financial** (Kas, Kategori, Jenis Simpanan/Pinjaman) | ✓ | ✓ | — | — | — | — | — |
| **Legal** — dokumen legal (upload/manage berkas) | ✓ | — | — | — | — | — | ✓ |
| **Kelola Pengguna** | ✓ | — | — | — | — | — | — |

### Button Visibility

Action buttons are hidden for unauthorized roles (even if they can view the page):
- **Tambah Pengajuan** — SUPER_ADMIN, ADMIN_DATA only
- **Edit Pengajuan** — SUPER_ADMIN, ADMIN_DATA only
- **Kelola Agunan** — SUPER_ADMIN, ADMIN_DATA only
- **Cetak Dokumen (PK/OL/Kwitansi)** — SUPER_ADMIN, TREASURY only
- **Bayar Angsuran** — SUPER_ADMIN, FINANCE only
- **Pelunasan** — SUPER_ADMIN, ADMIN_COLLECTION only
- **Tambah/Edit/Toggle Anggota** — SUPER_ADMIN, FINANCE, ADMIN_DATA only

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
   - ADMIN_DATA can edit applications they have access to
3. **Approval** — manager approves → `LoanService::approveAndDisburse()`
   - Creates cash expense (disbursement)
   - Creates `Loan` record with calculated schedule
   - Posts GL journal (DR Piutang, CR Kas)
4. **Kwitansi & Permohonan Dana** — print-ready documents: Kwitansi Plafon, Kwitansi Pencairan (with biaya-biaya JSON), Permohonan Dana
5. **Payments** — `LoanService::payInstallment()`
   - Auto-computes late penalty and early repayment penalty
   - Creates cash income, posts GL journal
   - Auto-closes loan when outstanding = 0
6. **Settlement / Jadwal / SubLedger** — accessed from the collection detail page (Admin Collection) via `/collection/{loan}/settlement`, `/schedule`, `/subledger/pdf`
7. **Void** — reverses payment + cash tx + journal entries

### Collection Workflow
1. **Master Tagihan** — overview table of all active loans with Progress Bayar (color-coded progress bar), Sisa Tagihan, tunggakan, and contact data
2. **Collection Detail** — per-loan page with catatan penagihan form, buat surat dropdown, letter history, and recent payments
3. **Escalation** — level 0 (Normal) → SP1 → SP2 → SP3 → Somasi → Pra-Cessie; each letter auto-increments the loan's escalation level
4. **Jadwal Angsuran / Amortisasi** — accessible from collection detail page header; shows full payment schedule with paid/overdue highlighting
5. **Pelunasan** — early settlement calculator with date picker, denda breakdown, and PDF; accessible from collection detail page header
6. **SubLedger** — per-loan ledger card PDF showing all payments and running balance; accessible from collection detail page header

### Void Audit Trail
All voidable entities track: `is_voided`, `void_reason`, `voided_by`, `voided_at`.
Void operations cascade through linked records (transaction → cash → journal).

---

## PDF Generation

| Document | Route | Engine |
|----------|-------|--------|
| SP I — Surat Peringatan I | `/collection/letters/{id}/pdf` | DomPDF |
| SP II — Surat Peringatan II | `/collection/letters/{id}/pdf` | DomPDF |
| SP III — Surat Peringatan III | `/collection/letters/{id}/pdf` | DomPDF |
| Somasi | `/collection/letters/{id}/pdf` | DomPDF |
| Pra-Cessie (Pengalihan Piutang) | `/collection/letters/{id}/pdf` | DomPDF |
| Loan Application Review | `/loans/applications/{id}/review-pdf` | DomPDF |
| Loan Agreement PK A (Sertipikat) | `/loans/applications/{id}/agreement` | DomPDF |
| Loan Agreement PK B (Kendaraan) | `/loans/applications/{id}/agreement-vehicle` | DomPDF |
| Penawaran Pembiayaan | `/loans/applications/{id}/penawaran` | DomPDF |
| Cash Voucher (RV/PV/TV) | `/cash/transactions/{tx}/voucher` | DomPDF |
| Savings Voucher | `/savings/transactions/{tx}/voucher` | DomPDF |
| Payment Receipt | `/loans/payments/{id}/receipt` | DomPDF |
| Loan Schedule (from Loans) | `/loans/active/{id}/schedule/pdf` | DomPDF |
| Loan Schedule (from Collection) | `/collection/{id}/schedule/pdf` | DomPDF |
| Settlement PDF (from Loans) | `/loans/active/{id}/settlement/pdf` | DomPDF |
| Settlement PDF (from Collection) | `/collection/{id}/settlement/pdf` | DomPDF |
| Sub-Ledger PDF (from Loans) | `/loans/active/{id}/subledger/pdf` | DomPDF |
| Sub-Ledger PDF (from Collection) | `/collection/{id}/subledger/pdf` | DomPDF |
| Kwitansi Plafon | `/loans/active/{id}/kwitansi-plafon` | `window.print()` |
| Kwitansi Pencairan | `/loans/active/{id}/kwitansi-pencairan` | `window.print()` |
| Permohonan Dana | `/loans/active/{id}/permohonan-dana` | `window.print()` |
| Cetak Semua Pemasukan | `/cash/income/print` | DomPDF |
| Cetak Semua Pengeluaran | `/cash/expense/print` | DomPDF |
| Cetak Semua Transfer | `/cash/transfer/print` | DomPDF |
| Deposito — Daftar (landscape) | `/deposito/print` | DomPDF |
| Deposito — Bilyet (A4 portrait) | `/deposito/{deposito}/print` | DomPDF |

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
| File upload validation | `mimes:jpg,jpeg,png,pdf`, `max:5120` (5 MB); loan files on `private` disk, legal docs on `public` disk |
| Mass assignment protection | All models use explicit `$fillable` arrays |
| Privilege escalation guard | `UserController::canManage()` — only SUPER_ADMIN can manage users; admin-assignable roles enforced |
| Public registration disabled | `/register` route removed in `routes/auth.php` |
| File ownership check | `deleteFile()` and `downloadFile()` verify `loan_application_id === application->id`; PETUGAS ownership enforced |
| MIME type integrity | File uploads store MIME from `getMimeType()` (reads actual file content via `finfo`), not client-supplied header |
| Rate limiting — profile/password | `PATCH /profile` throttle:10/min, `DELETE /profile` throttle:5/min, `PUT /password` throttle:5/min |

### Known Issues

None — all previously identified issues have been resolved.

---

## Reporting & Audit

| Report                    | Filters                                | Export  |
|---------------------------|----------------------------------------|---------|
| Master Tagihan Nasabah    | name, escalation level, petugas, sort | Print / CSV |
| Laporan Collection        | as-of date (always today)              | Print / CSV |
| Data Master DHIA          | borrower name, wilayah                 | Print / CSV |
| Cash Report               | date range, account, type              | CSV     |
| Savings Report            | date range, product, type              | CSV     |
| Loans Report              | date range, product, status            | CSV     |
| Trial Balance             | date range                             | —       |
| Income Statement          | date range                             | —       |
| Balance Sheet             | as-of date                             | —       |
| Void Audit — Cash         | date range                             | CSV     |
| Void Audit — Savings      | date range                             | CSV     |
| Void Audit — Loans        | date range                             | CSV     |
| Log Aktivitas (Audit Log) | action, module, user, date range       | CSV     |

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
# Option A — PRODUCTION (no demo data, clean slate)
php artisan migrate:fresh --seed --seeder=ProductionSeeder
php artisan ksp:create-superadmin    # Interactive: creates the first Super Admin account

# Option B — DEVELOPMENT: Quick setup (master data + demo accounts, no transactions)
php artisan db:seed --class=MasterConfigSeeder

# Option C — DEVELOPMENT: Full demo setup (master data + 6 months of transactions)
php artisan db:seed
```

Or run individual seeders (must follow this order):

```bash
php artisan db:seed --class=RolesSeeder              # 1. Create Spatie roles
php artisan db:seed --class=UsersSeeder              # 2. Create 10 team accounts
php artisan db:seed --class=DemoMasterSeeder         # 3. Members, cash accounts, products
php artisan db:seed --class=AccountingSeeder         # 4. Full COA + ledger auto-linking
php artisan db:seed --class=CashCategorySeeder       # 5. Cash categories with GL accounts
php artisan db:seed --class=DemoTransactionSeeder    # 6. 6 months of demo transactions (optional)
php artisan db:seed --class=CollectionOverdueSeeder  # 7. Simulate overdue loans for collection demo (optional)
```

> All seeders are idempotent — safe to re-run without creating duplicates.

### Testing Collection Overdue Buckets

After the full demo seed, run the **CollectionOverdueSeeder** to create overdue scenarios:

```bash
php artisan db:seed --class=CollectionOverdueSeeder
```

This reduces `total_paid` on the 6 oldest active loans to simulate 1–6 months of arrears. The Kolektabilitas table will then show loans distributed across the 1–30, 31–60, and 61+ hari buckets.

---

To manually control which bucket a specific loan falls into, use `php artisan tinker`:

```php
# --- PASTE INTO: php artisan tinker ---

use App\Models\Loan;
use Carbon\Carbon;

$today = Carbon::today();

// Get active loans (adjust ID as needed)
$loans = Loan::where('status', 'ACTIVE')->orderBy('disbursed_date')->get();

// Scenario map: [loan_index => months_to_backdate]
// Backdate disbursed_date so monthsElapsed > total paid months → overdue
$scenarios = [
    0 => 1,   // → 1–30 hari bucket
    1 => 2,   // → 1–30 or 31–60 hari (depending on day of month)
    2 => 3,   // → 31–60 hari
    3 => 4,   // → 61+ hari
    4 => 5,   // → 61+ hari
    5 => 6,   // → 61+ hari
];

foreach ($scenarios as $idx => $monthsBack) {
    $loan = $loans->get($idx);
    if (!$loan) continue;

    // Push disbursed_date back by $monthsBack extra months beyond what was paid
    $paidMonths = (int) floor($loan->total_paid / $loan->monthly_installment);
    $newDate    = $today->copy()->subMonths($paidMonths + $monthsBack + 1)->toDateString();

    \DB::table('loans')->where('id', $loan->id)->update([
        'disbursed_date' => $newDate,
    ]);

    echo "Loan #{$loan->id} ({$loan->member->name}): disbursed_date → {$newDate} ({$monthsBack} bln telat)\n";
}

echo "\nDone. Refresh Kolektabilitas page.\n";
```

**How it works:** the system computes overdue on-the-fly from `disbursed_date` + `total_paid` every page load — no stored "overdue flag". Pushing `disbursed_date` back increases `monthsElapsed`, making `expected > total_paid`, which populates the overdue buckets.

**To reset** (restore original disbursed dates), re-run `php artisan migrate:fresh --seed` or manually update `disbursed_date` back to the correct values via tinker.

### Production Deployment

```bash
# 1. Fresh database with system config only (roles, COA, cash categories)
php artisan migrate:fresh --seed --seeder=ProductionSeeder

# 2. Create the first Super Admin (interactive prompt for name, email, password)
php artisan ksp:create-superadmin

# 3. Super Admin logs in → Sistem → Manajemen User → creates all staff accounts
#    (FINANCE, ADMIN_DATA, ADMIN_COLLECTION, TREASURY, PENGAWAS, LEGAL)

# 4. Staff logs in and inputs real data: members, cash accounts, products, etc.
```

**ProductionSeeder** seeds only:
1. **RolesSeeder** — 7 system roles
2. **AccountingSeeder** — full 5-level COA (fixed accounts; dynamic linking runs when products are created)
3. **CashCategorySeeder** — 15 income/expense/transfer categories with GL links

No demo users, no demo members, no demo transactions.

### Artisan Commands

| Command | Description |
|---------|-------------|
| `ksp:create-superadmin` | Interactive command to create a Super Admin user (name, email, password with confirmation) |

### Demo Accounts

**Team accounts (UAT):**

| Email                | Password             | Role             | Name               |
|----------------------|----------------------|------------------|--------------------|
| superadmin@ksp.local | SuperAdmin@KSP2025   | SUPER_ADMIN      | Super Administrator|
| dilla@ksp.local      | Dilla@KSP2025        | FINANCE          | Dilla              |
| nadia@ksp.local      | Nadia@KSP2025        | FINANCE          | Nadia              |
| gio@ksp.local        | Gio@KSP2025          | ADMIN_DATA       | Gio                |
| adam@ksp.local       | Adam@KSP2025         | ADMIN_DATA       | Adam               |
| dia@ksp.local        | Dia@KSP2025          | ADMIN_COLLECTION | Dia                |
| franky@ksp.local     | Franky@KSP2025       | ADMIN_COLLECTION | Franky             |
| fitri@ksp.local      | Fitri@KSP2025        | TREASURY         | Fitri              |
| sri@ksp.local        | Sri@KSP2025          | PENGAWAS         | Sri                |
| evin@ksp.local       | Evin@KSP2025         | LEGAL            | Evin Marsela       |

> Registrasi publik dinonaktifkan. Akun baru hanya bisa dibuat oleh SUPER_ADMIN. Admin-assignable roles: FINANCE, ADMIN_DATA, ADMIN_COLLECTION, TREASURY, PENGAWAS, LEGAL.

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
- Extension migrations: penalty rates, notaris fields, form data snapshots, COA ledger links, cash categories, ledger uniqueness constraint, disbursement_fees JSON column, CHECK/GIRO account types
- Collection migrations: collection_records (escalation, petugas, property data), collection_letters
- M36 migration: `2026_03_12_000001_add_user_id_to_members_table` — nullable `user_id` FK for portal access
- M37 migration: `2026_03_12_000002_create_loan_collaterals_table` — collateral registry (type, value, document, HELD/RELEASED)
- M43 migration: `2026_03_12_100001_create_audit_logs_table` — system activity log
- M45 migrations: `legal_berkas` (document bundles) + `legal_documents` (individual files per berkas)
- M51 migration: `2026_04_08_110000_add_city_to_members_table` — adds nullable `city` column to members
- M52 migration: `2026_04_08_100000_drop_admin_fee_from_loan_tables` — removes `admin_fee` from `loan_products` and `loan_applications` (admin fees now sourced from `disbursement_fees` JSON)
- M62 migrations: `2026_04_23_100001_add_deposito_parent_account` — inserts COA 1160 "Penempatan Deposito"; `2026_04_23_100002_add_payment_method_to_cash_transactions` — adds nullable `payment_method` VARCHAR(20) to `cash_transactions`

---

## Training Guides

Built-in printable training guides for each role, accessible at `/admin/guide`. Each guide opens as a print-friendly HTML page with a "Cetak / Simpan PDF" button (browser print → Save as PDF).

| Route | Role | Content |
|-------|------|---------|
| `/admin/guide/super_admin` | SUPER_ADMIN | User management, loan approval, VOID, COA, SHU, collateral release |
| `/admin/guide/finance` | FINANCE | Kas (income/expense/transfer), simpanan, laporan, master data, akuntansi, audit |
| `/admin/guide/admin_data` | ADMIN_DATA | Pengajuan pinjaman, master anggota, agunan |
| `/admin/guide/admin_collection` | ADMIN_COLLECTION | Master tagihan, bayar angsuran, surat penagihan, DHIA, laporan collection |
| `/admin/guide/treasury` | TREASURY | Cetak PK (A/B), kwitansi, permohonan dana, jadwal angsuran |
| `/admin/guide/pengawas` | PENGAWAS | Read-only monitoring: pengajuan, pinjaman, collection, anggota |
| `/admin/guide/legal` | LEGAL | Dokumen legal, master tagihan, surat penagihan, DHIA |

A "Panduan" button is displayed on the dashboard for all users.

---

## Utility

### `terbilang($amount)`
Helper function in `app/helpers.php`. Converts a numeric amount to Indonesian words (e.g., `terbilang(1500000)` → `"satu juta lima ratus ribu"`). Used in kwitansi PDFs and settlement documents.
