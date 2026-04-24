<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CashTransactionController;
use App\Http\Controllers\Admin\SavingTransactionController;
use App\Http\Controllers\Admin\CashIncomeController;
use App\Http\Controllers\Admin\CashExpenseController;
use App\Http\Controllers\Admin\CashBalanceController;
use App\Http\Controllers\Admin\CashTransferController;
use App\Http\Controllers\Admin\SavingDepositController;
use App\Http\Controllers\Admin\SavingWithdrawController;
use App\Http\Controllers\Admin\DepositoController;
use App\Http\Controllers\Admin\LoanApplicationController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\LoanPaymentController;
use App\Http\Controllers\Admin\SavingVoidController;
use App\Http\Controllers\Admin\LoanPaymentVoidController;
use App\Http\Controllers\Admin\CashVoidController;
use App\Http\Controllers\Admin\Master\CashAccountController;
use App\Http\Controllers\Admin\Master\CashCategoryController;
use App\Http\Controllers\Admin\Master\MemberController;
use App\Http\Controllers\Admin\Master\SavingProductController;
use App\Http\Controllers\Admin\Master\LoanProductController;
use App\Http\Controllers\Admin\Audit\VoidAuditController;
use App\Http\Controllers\Admin\Audit\AuditLogController;
use App\Http\Controllers\Admin\Reports\CashReportController;
use App\Http\Controllers\Admin\Reports\SavingsReportController;
use App\Http\Controllers\Admin\Reports\LoansReportController;
use App\Http\Controllers\Admin\AccountingTrialBalanceController;
use App\Http\Controllers\Admin\Accounting\GeneralLedgerController;
use App\Http\Controllers\Admin\Accounting\JournalEntryController;
use App\Http\Controllers\Admin\Accounting\IncomeStatementController;
use App\Http\Controllers\Admin\Accounting\BalanceSheetController;
use App\Http\Controllers\Admin\Accounting\ShuController;
use App\Http\Controllers\Admin\Accounting\AccountController;
use App\Http\Controllers\Admin\MemberSavingsController;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Collection\CollectionController;
use App\Http\Controllers\Admin\Collection\CollectionLetterController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\LegalController;
use App\Http\Controllers\Admin\LoanCollateralController;
use App\Http\Controllers\Admin\TrainingGuideController;
use App\Http\Controllers\Portal\MemberPortalController;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user && $user->hasRole('MEMBER')) {
            return redirect()->route('portal.dashboard');
        }
        if ($user && $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN_DATA', 'ADMIN_COLLECTION', 'TREASURY', 'FINANCE', 'PENGAWAS', 'LEGAL'])) {
            return redirect()->route('admin.home');
        }
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:10,1')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:5,1')->name('profile.destroy');

    // =========================================================================
    // ADMIN AREA
    // Roles: SUPER_ADMIN, ADMIN_DATA, ADMIN_COLLECTION, TREASURY, FINANCE, PENGAWAS, LEGAL
    // =========================================================================
    Route::prefix('admin')
        ->middleware('role:SUPER_ADMIN|ADMIN_DATA|ADMIN_COLLECTION|TREASURY|FINANCE|PENGAWAS|LEGAL')
        ->group(function () {

            Route::get('/home', [AdminHomeController::class, 'index'])->name('admin.home');
            Route::get('/password/change', fn() => view('admin.password.change'))->name('admin.password.change');
            Route::get('/notifications', [NotificationController::class,'index'])->name('admin.notifications.index');

            // Training Guides
            Route::get('/guide', [TrainingGuideController::class, 'index'])->name('admin.guide.index');
            Route::get('/guide/{role}', [TrainingGuideController::class, 'show'])->name('admin.guide.show');

            // ------------------------------------------------------------------
            // KAS — SUPER_ADMIN only
            // ------------------------------------------------------------------
            Route::prefix('cash')->middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/income',          [CashIncomeController::class,  'index'])->name('admin.cash.income.index');
                Route::get('/income/print',    [CashIncomeController::class,  'printAll'])->name('admin.cash.income.print');
                Route::get('/income/create',   [CashIncomeController::class,  'create'])->name('admin.cash.income.create');
                Route::post('/income',         [CashIncomeController::class,  'store'])->name('admin.cash.income.store');
                Route::get('/expense',         [CashExpenseController::class, 'index'])->name('admin.cash.expense.index');
                Route::get('/expense/print',   [CashExpenseController::class, 'printAll'])->name('admin.cash.expense.print');
                Route::get('/expense/create',  [CashExpenseController::class, 'create'])->name('admin.cash.expense.create');
                Route::post('/expense',        [CashExpenseController::class, 'store'])->name('admin.cash.expense.store');
                Route::get('/transfer',        [CashTransferController::class,'index'])->name('admin.cash.transfer.index');
                Route::get('/transfer/print',  [CashTransferController::class,'printAll'])->name('admin.cash.transfer.print');
                Route::get('/transfer/create', [CashTransferController::class,'create'])->name('admin.cash.transfer.create');
                Route::post('/transfer',       [CashTransferController::class,'store'])->name('admin.cash.transfer.store');
                Route::get('/transactions/{tx}/void',  [CashVoidController::class,'create'])->name('admin.cash.void.create');
                Route::post('/transactions/{tx}/void', [CashVoidController::class,'store'])->name('admin.cash.void.store');
                Route::get('/transactions/{tx}',         [CashTransactionController::class,'show'])->name('admin.cash.transactions.show');
                Route::get('/transactions/{tx}/voucher', [CashTransactionController::class,'voucher'])->name('admin.cash.transactions.voucher');
            });

            Route::get('/cash/balances', [CashBalanceController::class, 'index'])
                ->middleware('role:SUPER_ADMIN|FINANCE')
                ->name('admin.cash.balances.index');

            // ------------------------------------------------------------------
            // SIMPANAN — SUPER_ADMIN + FINANCE
            // ------------------------------------------------------------------
            Route::middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/savings/transactions/{tx}',         [SavingTransactionController::class,'show'])->name('admin.savings.transactions.show');
                Route::get('/savings/transactions/{tx}/voucher', [SavingTransactionController::class,'voucher'])->name('admin.savings.transactions.voucher');

                Route::get('/savings/deposit',        [SavingDepositController::class,  'index'])->name('admin.savings.deposit.index');
                Route::get('/savings/deposit/create', [SavingDepositController::class,  'create'])->name('admin.savings.deposit.create');
                Route::post('/savings/deposit',       [SavingDepositController::class,  'store'])->name('admin.savings.deposit.store');
                Route::get('/savings/withdraw',       [SavingWithdrawController::class, 'index'])->name('admin.savings.withdraw.index');
                Route::get('/savings/withdraw/create',[SavingWithdrawController::class, 'create'])->name('admin.savings.withdraw.create');
                Route::post('/savings/withdraw',      [SavingWithdrawController::class, 'store'])->name('admin.savings.withdraw.store');

                Route::get('/savings/transactions/{transaction}/void',  [SavingVoidController::class,'create'])->name('admin.savings.void.create');
                Route::post('/savings/transactions/{transaction}/void', [SavingVoidController::class,'store'])->name('admin.savings.void.store');
            });

            Route::prefix('savings/member-balance')
                ->middleware('role:SUPER_ADMIN|FINANCE')
                ->group(function () {
                    Route::get('/',         [MemberSavingsController::class,'index'])->name('admin.savings.member_balance.index');
                    Route::get('/{member}', [MemberSavingsController::class,'show'])->name('admin.savings.member_balance.show');
                });

            // ------------------------------------------------------------------
            // DEPOSITO — SUPER_ADMIN + FINANCE
            // Static routes (create) BEFORE parameterized ({deposito}) routes.
            // ------------------------------------------------------------------
            Route::prefix('deposito')->middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/',                  [DepositoController::class, 'index'])->name('admin.deposito.index');
                Route::get('/create',            [DepositoController::class, 'create'])->name('admin.deposito.create');
                Route::get('/print',             [DepositoController::class, 'printIndex'])->name('admin.deposito.print.index');
                Route::post('/',                 [DepositoController::class, 'store'])->name('admin.deposito.store');
                Route::get('/{deposito}',        [DepositoController::class, 'show'])->name('admin.deposito.show');
                Route::get('/{deposito}/edit',   [DepositoController::class, 'edit'])->name('admin.deposito.edit');
                Route::put('/{deposito}',        [DepositoController::class, 'update'])->name('admin.deposito.update');
                Route::get('/{deposito}/print',  [DepositoController::class, 'printShow'])->name('admin.deposito.print.show');
                Route::post('/{deposito}/cair',  [DepositoController::class, 'cair'])->name('admin.deposito.cair');
            });

            // ------------------------------------------------------------------
            // PINJAMAN
            // ------------------------------------------------------------------

            // Loan applications — create/store must be before {application} routes to avoid "create" matching as an ID
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA')->group(function () {
                Route::get('/loans/applications/create',  [LoanApplicationController::class,'create'])->name('admin.loans.applications.create');
                Route::post('/loans/applications',        [LoanApplicationController::class,'store'])->name('admin.loans.applications.store');
            });

            // Loan applications — list: LEGAL + FINANCE can see overview
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA|TREASURY|PENGAWAS|LEGAL|FINANCE')->group(function () {
                Route::get('/loans/applications', [LoanApplicationController::class,'index'])->name('admin.loans.applications.index');
            });
            // Loan applications — detail: LEGAL + FINANCE can view
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA|TREASURY|PENGAWAS|LEGAL|FINANCE')->group(function () {
                Route::get('/loans/applications/{application}', [LoanApplicationController::class,'show'])->name('admin.loans.applications.show');
                Route::get('/loans/applications/{application}/files/{file}/download',[LoanApplicationController::class,'downloadFile'])->name('admin.loans.applications.files.download');
            });
            // Loan application PDFs — LEGAL excluded
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA|TREASURY|PENGAWAS')->group(function () {
                Route::get('/loans/applications/{application}/review-pdf',[LoanApplicationController::class,'reviewPdf'])->name('admin.loans.applications.review_pdf');
                Route::get('/loans/applications/{application}/penawaran-pembiayaan',[LoanApplicationController::class,'penawaranPembiayaan'])->name('admin.loans.applications.penawaran');
                Route::get('/loans/applications/{application}/agreement',[LoanApplicationController::class,'agreement'])->name('admin.loans.applications.agreement');
                Route::get('/loans/applications/{application}/agreement-vehicle',[LoanApplicationController::class,'agreementVehicle'])->name('admin.loans.applications.agreement.vehicle');
                Route::get('/loans/applications/{application}/agreement-word',[LoanApplicationController::class,'agreementWord'])->name('admin.loans.applications.agreement.word');
                Route::get('/loans/applications/{application}/agreement-vehicle-word',[LoanApplicationController::class,'agreementVehicleWord'])->name('admin.loans.applications.agreement.vehicle.word');
            });

            // Loan applications — write: ADMIN_DATA only
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA')->group(function () {
                Route::get('/loans/applications/{application}/edit', [LoanApplicationController::class,'edit'])->name('admin.loans.applications.edit');
                Route::put('/loans/applications/{application}',      [LoanApplicationController::class,'update'])->name('admin.loans.applications.update');
                Route::delete('/loans/applications/{application}/files/{file}', [LoanApplicationController::class,'deleteFile'])->name('admin.loans.applications.files.delete');
            });

            // Finance review — FINANCE sets interest/method before approval
            Route::middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::post('/loans/applications/{application}/finance-review', [LoanApplicationController::class,'financeReview'])->name('admin.loans.applications.finance-review');
            });

            // Edit personal info & approve/reject — SUPER_ADMIN only
            Route::middleware('role:SUPER_ADMIN')->group(function () {
                Route::get('/loans/applications/{application}/edit-personal', [LoanApplicationController::class,'editPersonalInfo'])->name('admin.loans.applications.edit_personal');
                Route::put('/loans/applications/{application}/personal',      [LoanApplicationController::class,'updatePersonalInfo'])->name('admin.loans.applications.update_personal');
                Route::post('/loans/applications/{application}/approve',[LoanApplicationController::class,'approve'])->name('admin.loans.applications.approve');
                Route::post('/loans/applications/{application}/reject', [LoanApplicationController::class,'reject'])->name('admin.loans.applications.reject');
            });

            // Active loans index
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA|ADMIN_COLLECTION|FINANCE|PENGAWAS|LEGAL')->group(function () {
                Route::get('/loans/active', [LoanController::class,'index'])->name('admin.loans.active.index');
            });

            // Active loan show — LEGAL can view
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA|ADMIN_COLLECTION|FINANCE|PENGAWAS|LEGAL')->group(function () {
                Route::get('/loans/active/{loan}',                 [LoanController::class,'show'])->name('admin.loans.active.show');
            });
            // Schedule/subledger/settlement — LEGAL excluded
            Route::middleware('role:SUPER_ADMIN|ADMIN_DATA|ADMIN_COLLECTION|FINANCE|TREASURY|PENGAWAS')->group(function () {
                Route::get('/loans/active/{loan}/schedule',        [LoanController::class,'schedule'])->name('admin.loans.active.schedule');
                Route::get('/loans/active/{loan}/schedule/pdf',    [LoanController::class,'schedulePdf'])->name('admin.loans.active.schedule.pdf');
                Route::get('/loans/active/{loan}/subledger/pdf',   [LoanController::class,'subledgerPdf'])->name('admin.loans.active.subledger.pdf');
                Route::get('/loans/active/{loan}/settlement',      [LoanController::class,'settlement'])->name('admin.loans.active.settlement');
                Route::get('/loans/active/{loan}/settlement/pdf',  [LoanController::class,'settlementPdf'])->name('admin.loans.active.settlement.pdf');
            });

            // Kwitansi & Permohonan Dana — TREASURY + LEGAL + PENGAWAS (read-only)
            Route::middleware('role:SUPER_ADMIN|TREASURY|LEGAL|PENGAWAS')->group(function () {
                Route::get('/loans/active/{loan}/kwitansi-pencairan', [LoanController::class,'kwitansiPencairan'])->name('admin.loans.active.kwitansi.pencairan');
                Route::get('/loans/active/{loan}/kwitansi-plafon',    [LoanController::class,'kwitansiPlafon'])->name('admin.loans.active.kwitansi.plafon');
                Route::get('/loans/active/{loan}/kwitansi-biaya',     [LoanController::class,'kwitansiBiaya'])->name('admin.loans.active.kwitansi.biaya');
                Route::get('/loans/active/{loan}/permohonan-dana',     [LoanController::class,'permohonanDana'])->name('admin.loans.active.permohonan.dana');
                Route::get('/loans/active/{loan}/permohonan-dana/pdf', [LoanController::class,'permohonanDanaPdf'])->name('admin.loans.active.permohonan.dana.pdf');
            });
            // Kwitansi Pencairan & Permohonan Dana save — TREASURY only (write operation)
            Route::middleware('role:SUPER_ADMIN|TREASURY')->group(function () {
                Route::post('/loans/active/{loan}/kwitansi-pencairan',[LoanController::class,'saveKwitansiPencairan'])->name('admin.loans.active.kwitansi.pencairan.save');
                Route::post('/loans/active/{loan}/kwitansi-biaya',    [LoanController::class,'kwitansiBiayaPrint'])->name('admin.loans.active.kwitansi.biaya.print');
                Route::post('/loans/active/{loan}/permohonan-dana',   [LoanController::class,'savePermohonanDana'])->name('admin.loans.active.permohonan.dana.save');
            });

            // Loan payments — FINANCE
            Route::middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/loans/active/{loan}/payments/create',[LoanPaymentController::class,'create'])->name('admin.loans.payments.create');
                Route::post('/loans/active/{loan}/payments',      [LoanPaymentController::class,'store'])->name('admin.loans.payments.store');
                Route::get('/loans/payments/{payment}/receipt',   [LoanPaymentController::class,'receipt'])->name('admin.loans.payments.receipt');
            });

            // Payment void — SUPER_ADMIN only
            Route::middleware('role:SUPER_ADMIN')->group(function () {
                Route::get('/loans/payments/{payment}/void',  [LoanPaymentVoidController::class,'create'])->name('admin.loans.payments.void.create');
                Route::post('/loans/payments/{payment}/void', [LoanPaymentVoidController::class,'store'])->name('admin.loans.payments.void.store');
            });

            // ------------------------------------------------------------------
            // AKUNTANSI — SUPER_ADMIN + FINANCE
            // ------------------------------------------------------------------
            Route::prefix('accounting/accounts')->middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/',                  [AccountController::class,'index'])->name('admin.accounting.accounts.index');
                // Finance may add/edit accounts (e.g. Advance, new beban accounts).
                Route::get('/create',            [AccountController::class,'create'])->name('admin.accounting.accounts.create');
                Route::post('/',                 [AccountController::class,'store'])->name('admin.accounting.accounts.store');
                Route::get('/{account}/edit',    [AccountController::class,'edit'])->name('admin.accounting.accounts.edit');
                Route::put('/{account}',         [AccountController::class,'update'])->name('admin.accounting.accounts.update');
                // Toggle active/inactive remains SUPER_ADMIN only.
                Route::middleware('role:SUPER_ADMIN')->group(function () {
                    Route::patch('/{account}/toggle',[AccountController::class,'toggle'])->name('admin.accounting.accounts.toggle');
                });
            });

            Route::middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/accounting/trial-balance',           [AccountingTrialBalanceController::class,'index'])->name('admin.accounting.trial_balance.index');
                Route::get('/accounting/trial-balance/export',    [AccountingTrialBalanceController::class,'export'])->name('admin.accounting.trial_balance.export');
                Route::get('/accounting/general-ledger',          [GeneralLedgerController::class,'index'])->name('admin.accounting.general_ledger.index');
                Route::get('/accounting/general-ledger/export',   [GeneralLedgerController::class,'export'])->name('admin.accounting.general_ledger.export');
                Route::get('/accounting/journal-entries',         [JournalEntryController::class,'index'])->name('admin.accounting.journal_entries.index');
                Route::get('/accounting/journal-entries/export',  [JournalEntryController::class,'export'])->name('admin.accounting.journal_entries.export');
                Route::get('/accounting/journal-entries/{entry}', [JournalEntryController::class,'show'])->name('admin.accounting.journal_entries.show');
                Route::get('/accounting/income-statement',        [IncomeStatementController::class,'index'])->name('admin.accounting.income_statement.index');
                Route::get('/accounting/income-statement/export', [IncomeStatementController::class,'export'])->name('admin.accounting.income_statement.export');
                Route::get('/accounting/balance-sheet',           [BalanceSheetController::class,'index'])->name('admin.accounting.balance_sheet.index');
                Route::get('/accounting/balance-sheet/export',    [BalanceSheetController::class,'export'])->name('admin.accounting.balance_sheet.export');
            });

            Route::prefix('shu')->middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/',            [ShuController::class,'index'])->name('admin.shu.index');
                Route::get('/{shuPeriod}', [ShuController::class,'show'])->name('admin.shu.show');
                Route::middleware('role:SUPER_ADMIN')->group(function () {
                    Route::get('/create',                    [ShuController::class,'create'])->name('admin.shu.create');
                    Route::post('/',                         [ShuController::class,'store'])->name('admin.shu.store');
                    Route::patch('/{shuPeriod}/recalculate', [ShuController::class,'recalculate'])->name('admin.shu.recalculate');
                    Route::post('/{shuPeriod}/finalize',     [ShuController::class,'finalize'])->name('admin.shu.finalize');
                    Route::delete('/{shuPeriod}',            [ShuController::class,'destroy'])->name('admin.shu.destroy');
                });
            });

            // ------------------------------------------------------------------
            // AUDIT — SUPER_ADMIN + FINANCE
            // ------------------------------------------------------------------
            Route::prefix('audit')->middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/void/cash',                 [VoidAuditController::class,'cash'])->name('admin.audit.void.cash');
                Route::get('/void/savings',              [VoidAuditController::class,'savings'])->name('admin.audit.void.savings');
                Route::get('/void/loan-payments',        [VoidAuditController::class,'loanPayments'])->name('admin.audit.void.loan_payments');
                Route::get('/void/cash/export',          [VoidAuditController::class,'exportCash'])->name('admin.audit.void.cash.export');
                Route::get('/void/savings/export',       [VoidAuditController::class,'exportSavings'])->name('admin.audit.void.savings.export');
                Route::get('/void/loan-payments/export', [VoidAuditController::class,'exportLoanPayments'])->name('admin.audit.void.loan_payments.export');
                Route::get('/log',        [AuditLogController::class,'index'])->name('admin.audit.log.index');
                Route::get('/log/export', [AuditLogController::class,'export'])->name('admin.audit.log.export');
            });

            // ------------------------------------------------------------------
            // LAPORAN — SUPER_ADMIN + FINANCE
            // ------------------------------------------------------------------
            Route::prefix('reports')->middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                Route::get('/cash',           [CashReportController::class,   'index'])->name('admin.reports.cash');
                Route::get('/savings',        [SavingsReportController::class,'index'])->name('admin.reports.savings');
                Route::get('/loans',          [LoansReportController::class,  'index'])->name('admin.reports.loans');
                Route::get('/cash/export',    [CashReportController::class,   'export'])->name('admin.reports.cash.export');
                Route::get('/cash/print',     [CashReportController::class,   'print'])->name('admin.reports.cash.print');
                Route::get('/savings/export', [SavingsReportController::class,'export'])->name('admin.reports.savings.export');
                Route::get('/loans/export',   [LoansReportController::class,  'export'])->name('admin.reports.loans.export');
            });

            // ------------------------------------------------------------------
            // MASTER DATA
            // Financial items (kas, simpanan, pinjaman products): SUPER_ADMIN + FINANCE
            // Anggota: broader access (no money amounts)
            // ------------------------------------------------------------------
            Route::prefix('master')->group(function () {

                // Anggota — viewable by ADMIN_DATA, ADMIN_COLLECTION, PENGAWAS, LEGAL too
                Route::middleware('role:SUPER_ADMIN|FINANCE|ADMIN_DATA|ADMIN_COLLECTION|PENGAWAS|LEGAL')->group(function () {
                    Route::get('/members', [MemberController::class,'index'])->name('admin.master.members.index');
                });

                // Anggota write — SUPER_ADMIN + FINANCE + ADMIN_DATA
                Route::middleware('role:SUPER_ADMIN|FINANCE|ADMIN_DATA')->group(function () {
                    Route::get('/members/create',        [MemberController::class,'create'])->name('admin.master.members.create');
                    Route::post('/members',              [MemberController::class,'store'])->name('admin.master.members.store');
                    Route::get('/members/{member}/edit', [MemberController::class,'edit'])->name('admin.master.members.edit');
                    Route::put('/members/{member}',      [MemberController::class,'update'])->name('admin.master.members.update');
                    Route::patch('/members/{member}/toggle',[MemberController::class,'toggle'])->name('admin.master.members.toggle');
                });

                // Financial master data — SUPER_ADMIN + FINANCE only
                Route::middleware('role:SUPER_ADMIN|FINANCE')->group(function () {
                    // Read-only indexes
                    Route::get('/cash-categories',  [CashCategoryController::class,'index'])->name('admin.master.cash_categories.index');
                    Route::get('/cash-accounts',    [CashAccountController::class,'index'])->name('admin.master.cash_accounts.index');
                    Route::get('/saving-products',  [SavingProductController::class,'index'])->name('admin.master.saving_products.index');
                    Route::get('/loan-products',    [LoanProductController::class,'index'])->name('admin.master.loan_products.index');

                    // Write operations
                    Route::get('/cash-categories/create',              [CashCategoryController::class,'create'])->name('admin.master.cash_categories.create');
                    Route::post('/cash-categories',                    [CashCategoryController::class,'store'])->name('admin.master.cash_categories.store');
                    Route::get('/cash-categories/{cashCategory}/edit', [CashCategoryController::class,'edit'])->name('admin.master.cash_categories.edit');
                    Route::put('/cash-categories/{cashCategory}',      [CashCategoryController::class,'update'])->name('admin.master.cash_categories.update');
                    Route::patch('/cash-categories/{cashCategory}/toggle',[CashCategoryController::class,'toggle'])->name('admin.master.cash_categories.toggle');

                    Route::get('/cash-accounts/create',            [CashAccountController::class,'create'])->name('admin.master.cash_accounts.create');
                    Route::post('/cash-accounts',                  [CashAccountController::class,'store'])->name('admin.master.cash_accounts.store');
                    Route::get('/cash-accounts/{cashAccount}/edit',[CashAccountController::class,'edit'])->name('admin.master.cash_accounts.edit');
                    Route::put('/cash-accounts/{cashAccount}',     [CashAccountController::class,'update'])->name('admin.master.cash_accounts.update');
                    Route::patch('/cash-accounts/{cashAccount}/toggle',[CashAccountController::class,'toggle'])->name('admin.master.cash_accounts.toggle');

                    Route::get('/saving-products/create',              [SavingProductController::class,'create'])->name('admin.master.saving_products.create');
                    Route::post('/saving-products',                    [SavingProductController::class,'store'])->name('admin.master.saving_products.store');
                    Route::get('/saving-products/{savingProduct}/edit',[SavingProductController::class,'edit'])->name('admin.master.saving_products.edit');
                    Route::put('/saving-products/{savingProduct}',     [SavingProductController::class,'update'])->name('admin.master.saving_products.update');
                    Route::patch('/saving-products/{savingProduct}/toggle',[SavingProductController::class,'toggle'])->name('admin.master.saving_products.toggle');

                    Route::get('/loan-products/create',            [LoanProductController::class,'create'])->name('admin.master.loan_products.create');
                    Route::post('/loan-products',                  [LoanProductController::class,'store'])->name('admin.master.loan_products.store');
                    Route::get('/loan-products/{loanProduct}/edit',[LoanProductController::class,'edit'])->name('admin.master.loan_products.edit');
                    Route::put('/loan-products/{loanProduct}',     [LoanProductController::class,'update'])->name('admin.master.loan_products.update');
                    Route::patch('/loan-products/{loanProduct}/toggle',[LoanProductController::class,'toggle'])->name('admin.master.loan_products.toggle');
                });
            });

            // ------------------------------------------------------------------
            // LEGAL — Document management (SUPER_ADMIN + LEGAL only)
            // ------------------------------------------------------------------
            Route::prefix('legal')->middleware('role:SUPER_ADMIN|LEGAL')->group(function () {
                Route::get('/',                          [LegalController::class, 'index'])->name('admin.legal.index');
                Route::get('/create',                    [LegalController::class, 'create'])->name('admin.legal.create');
                Route::post('/',                         [LegalController::class, 'store'])->name('admin.legal.store');
                Route::get('/{berkas}',                  [LegalController::class, 'show'])->name('admin.legal.show');
                Route::post('/{berkas}/files',           [LegalController::class, 'addFiles'])->name('admin.legal.addFiles');
                Route::delete('/{berkas}/files/{document}', [LegalController::class, 'destroyFile'])->name('admin.legal.file.destroy');
                Route::delete('/{berkas}',               [LegalController::class, 'destroy'])->name('admin.legal.destroy');
            });

            // ------------------------------------------------------------------
            // ADMIN COLLECTION (PENAGIHAN)
            // Full access: SUPER_ADMIN + ADMIN_COLLECTION + PENGAWAS
            // DHIA only:   + TREASURY
            // ------------------------------------------------------------------
            Route::prefix('collection')->group(function () {

                // DHIA — TREASURY + PENGAWAS + LEGAL + ADMIN_DATA read-only view
                Route::middleware('role:SUPER_ADMIN|ADMIN_COLLECTION|ADMIN_DATA|LEGAL|TREASURY|PENGAWAS')->group(function () {
                    Route::get('/master-dhia',  [CollectionController::class,'masterDhia'])->name('admin.collection.master-dhia');
                    Route::get('/export-dhia',  [CollectionController::class,'exportMasterDhia'])->name('admin.collection.export-dhia');
                });

                // Collection overview — LEGAL can see list + laporan only
                Route::middleware('role:SUPER_ADMIN|ADMIN_COLLECTION|PENGAWAS|LEGAL')->group(function () {
                    Route::get('/',               [CollectionController::class,'index'])->name('admin.collection.index');
                    Route::get('/export',         [CollectionController::class,'exportIndex'])->name('admin.collection.export');
                    Route::get('/laporan',        [CollectionController::class,'laporan'])->name('admin.collection.laporan');
                    Route::get('/export-laporan', [CollectionController::class,'exportLaporan'])->name('admin.collection.export-laporan');
                });

                // Collection detail + write + PDFs — LEGAL + PENGAWAS excluded
                Route::middleware('role:SUPER_ADMIN|ADMIN_COLLECTION')->group(function () {
                    Route::get('/letters/{letter}/pdf', [CollectionLetterController::class,'pdf'])->name('admin.collection.letters.pdf');
                    Route::get('/{loan}',               [CollectionController::class,'show'])->name('admin.collection.show');
                    Route::patch('/{loan}',             [CollectionController::class,'update'])->name('admin.collection.update');
                    Route::post('/{loan}/letters',      [CollectionLetterController::class,'store'])->name('admin.collection.letters.store');

                    // Collection-context routes for schedule, subledger, settlement
                    Route::get('/{loan}/schedule',        [LoanController::class,'schedule'])->name('admin.collection.schedule');
                    Route::get('/{loan}/schedule/pdf',    [LoanController::class,'schedulePdf'])->name('admin.collection.schedule.pdf');
                    Route::get('/{loan}/subledger/pdf',   [LoanController::class,'subledgerPdf'])->name('admin.collection.subledger.pdf');
                    Route::get('/{loan}/settlement',      [LoanController::class,'settlement'])->name('admin.collection.settlement');
                    Route::get('/{loan}/settlement/pdf',  [LoanController::class,'settlementPdf'])->name('admin.collection.settlement.pdf');
                });
            });

            // ------------------------------------------------------------------
            // USER MANAGEMENT — SUPER_ADMIN only
            // ------------------------------------------------------------------
            Route::prefix('users')->middleware('role:SUPER_ADMIN')->group(function () {
                Route::get('/',            [UserController::class,'index'])->name('admin.users.index');
                Route::get('/create',      [UserController::class,'create'])->name('admin.users.create');
                Route::post('/',           [UserController::class,'store'])->name('admin.users.store');
                Route::get('/{user}/edit', [UserController::class,'edit'])->name('admin.users.edit');
                Route::put('/{user}',      [UserController::class,'update'])->name('admin.users.update');
                Route::delete('/{user}',   [UserController::class,'destroy'])->name('admin.users.destroy');
            });

            // ------------------------------------------------------------------
            // AGUNAN (COLLATERAL REGISTRY)
            // ------------------------------------------------------------------
            Route::prefix('loans/applications/{application}/collaterals')
                ->middleware('role:SUPER_ADMIN|ADMIN_DATA')
                ->group(function () {
                    Route::get('/',                      [LoanCollateralController::class,'index'])->name('admin.loans.applications.collaterals.index');
                    Route::get('/create',                [LoanCollateralController::class,'create'])->name('admin.loans.applications.collaterals.create');
                    Route::post('/',                     [LoanCollateralController::class,'store'])->name('admin.loans.applications.collaterals.store');
                    Route::get('/{collateral}/edit',     [LoanCollateralController::class,'edit'])->name('admin.loans.applications.collaterals.edit');
                    Route::put('/{collateral}',          [LoanCollateralController::class,'update'])->name('admin.loans.applications.collaterals.update');
                    Route::get('/{collateral}/download', [LoanCollateralController::class,'download'])->name('admin.loans.applications.collaterals.download');
                    Route::middleware('role:SUPER_ADMIN')->group(function () {
                        Route::get('/{collateral}/release',   [LoanCollateralController::class,'releaseForm'])->name('admin.loans.applications.collaterals.release-form');
                        Route::patch('/{collateral}/release', [LoanCollateralController::class,'release'])->name('admin.loans.applications.collaterals.release');
                        Route::delete('/{collateral}',        [LoanCollateralController::class,'destroy'])->name('admin.loans.applications.collaterals.destroy');
                    });
                });
        });

    // ------------------------------------------------------------------
    // PORTAL ANGGOTA — users with MEMBER role
    // ------------------------------------------------------------------
    Route::prefix('portal')
        ->middleware(['role:MEMBER'])
        ->group(function () {
            Route::get('/',           [MemberPortalController::class,'dashboard'])->name('portal.dashboard');
            Route::get('/savings',    [MemberPortalController::class,'savings'])->name('portal.savings');
            Route::get('/deposito',   [MemberPortalController::class,'deposito'])->name('portal.deposito');
            Route::get('/loans',      [MemberPortalController::class,'loans'])->name('portal.loans');
            Route::get('/not-linked', [MemberPortalController::class,'notLinked'])->name('portal.not-linked');
        });
});

require __DIR__ . '/auth.php';
