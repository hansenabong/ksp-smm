<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CashIncomeController;
use App\Http\Controllers\Admin\CashExpenseController;
use App\Http\Controllers\Admin\CashBalanceController;
use App\Http\Controllers\Admin\CashTransferController;
use App\Http\Controllers\Admin\SavingDepositController;
use App\Http\Controllers\Admin\SavingWithdrawController;
use App\Http\Controllers\Admin\LoanApplicationController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\LoanPaymentController;
use App\Http\Controllers\Admin\SavingVoidController;
use App\Http\Controllers\Admin\LoanPaymentVoidController;
use App\Http\Controllers\Admin\CashVoidController;
use App\Http\Controllers\Admin\Master\CashAccountController;
use App\Http\Controllers\Admin\Master\MemberController;
use App\Http\Controllers\Admin\Master\SavingProductController;
use App\Http\Controllers\Admin\Master\LoanProductController;
use App\Http\Controllers\Admin\Audit\VoidAuditController;
use App\Http\Controllers\Admin\Reports\CashReportController;
use App\Http\Controllers\Admin\Reports\SavingsReportController;
use App\Http\Controllers\Admin\Reports\LoansReportController;
use App\Http\Controllers\Admin\AccountingTrialBalanceController;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user && $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER', 'PETUGAS'])) {
            return redirect()->route('admin.home');
        }

        return view('dashboard'); // nanti portal anggota
    })->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Admin area (petugas)
    Route::prefix('admin')
        ->middleware('role:SUPER_ADMIN|ADMIN|BENDAHARA|MANAGER|PETUGAS')
        ->group(function () {
            Route::view('/home', 'admin.home')->name('admin.home');
            Route::get('/cash/income', [CashIncomeController::class, 'index'])->name('admin.cash.income.index');
            Route::get('/cash/income/create', [CashIncomeController::class, 'create'])->name('admin.cash.income.create');
            Route::post('/cash/income', [CashIncomeController::class, 'store'])->name('admin.cash.income.store');
            Route::get('/cash/expense', [CashExpenseController::class, 'index'])->name('admin.cash.expense.index');
            Route::get('/cash/expense/create', [CashExpenseController::class, 'create'])->name('admin.cash.expense.create');
            Route::post('/cash/expense', [CashExpenseController::class, 'store'])->name('admin.cash.expense.store');
            Route::get('/cash/balances', [CashBalanceController::class, 'index'])->name('admin.cash.balances.index');
            Route::get('/cash/transfer', [CashTransferController::class, 'index'])->name('admin.cash.transfer.index');
            Route::get('/cash/transfer/create', [CashTransferController::class, 'create'])->name('admin.cash.transfer.create');
            Route::post('/cash/transfer', [CashTransferController::class, 'store'])->name('admin.cash.transfer.store');
            Route::get('/savings/deposit', [SavingDepositController::class, 'index'])->name('admin.savings.deposit.index');
            Route::get('/savings/deposit/create', [SavingDepositController::class, 'create'])->name('admin.savings.deposit.create');
            Route::post('/savings/deposit', [SavingDepositController::class, 'store'])->name('admin.savings.deposit.store');
            Route::get('/savings/withdraw', [SavingWithdrawController::class, 'index'])->name('admin.savings.withdraw.index');
            Route::get('/savings/withdraw/create', [SavingWithdrawController::class, 'create'])->name('admin.savings.withdraw.create');
            Route::post('/savings/withdraw', [SavingWithdrawController::class, 'store'])->name('admin.savings.withdraw.store');
            Route::get('/loans/applications', [LoanApplicationController::class, 'index'])->name('admin.loans.applications.index');
            Route::get('/loans/applications/create', [LoanApplicationController::class, 'create'])->name('admin.loans.applications.create');
            Route::post('/loans/applications', [LoanApplicationController::class, 'store'])->name('admin.loans.applications.store');
            Route::get('/loans/applications/{application}', [LoanApplicationController::class, 'show'])->name('admin.loans.applications.show');
            Route::post('/loans/applications/{application}/approve', [LoanApplicationController::class, 'approve'])->name('admin.loans.applications.approve');
            Route::post('/loans/applications/{application}/reject', [LoanApplicationController::class, 'reject'])->name('admin.loans.applications.reject');
            Route::get('/loans/active', [LoanController::class, 'index'])->name('admin.loans.active.index');
            Route::get('/loans/active/{loan}', [LoanController::class, 'show'])->name('admin.loans.active.show');
            Route::get('/loans/active/{loan}/payments/create', [LoanPaymentController::class, 'create'])->name('admin.loans.payments.create');
            Route::post('/loans/active/{loan}/payments', [LoanPaymentController::class, 'store'])->name('admin.loans.payments.store');
            Route::get('/loans/payments/{payment}/receipt', [LoanPaymentController::class, 'receipt'])->name('admin.loans.payments.receipt');
            Route::get('/savings/transactions/{transaction}/void', [SavingVoidController::class, 'create'])->name('admin.savings.void.create');
            Route::post('/savings/transactions/{transaction}/void', [SavingVoidController::class, 'store'])->name('admin.savings.void.store');
            Route::get('/loans/payments/{payment}/void', [LoanPaymentVoidController::class, 'create'])->name('admin.loans.payments.void.create');
            Route::post('/loans/payments/{payment}/void', [LoanPaymentVoidController::class, 'store'])->name('admin.loans.payments.void.store');
            Route::get('/cash/transactions/{tx}/void', [CashVoidController::class, 'create'])->name('admin.cash.void.create');
            Route::post('/cash/transactions/{tx}/void', [CashVoidController::class, 'store'])->name('admin.cash.void.store');
            Route::get('/accounting/trial-balance', [AccountingTrialBalanceController::class, 'index'])->name('admin.accounting.trial_balance.index');
            Route::get('/loans/applications/{application}/files/{file}/download', [LoanApplicationController::class, 'downloadFile'])->name('admin.loans.applications.files.download');
            Route::get('/loans/applications/{application}/agreement', [LoanApplicationController::class, 'agreement'])->name('admin.loans.applications.agreement');
            Route::prefix('master')
                ->middleware('role:SUPER_ADMIN|ADMIN|BENDAHARA|MANAGER')
                ->group(function () {
                    Route::get('/cash-accounts', [CashAccountController::class, 'index'])->name('admin.master.cash_accounts.index');
                    Route::get('/cash-accounts/create', [CashAccountController::class, 'create'])->name('admin.master.cash_accounts.create');
                    Route::post('/cash-accounts', [CashAccountController::class, 'store'])->name('admin.master.cash_accounts.store');
                    Route::get('/cash-accounts/{cashAccount}/edit', [CashAccountController::class, 'edit'])->name('admin.master.cash_accounts.edit');
                    Route::put('/cash-accounts/{cashAccount}', [CashAccountController::class, 'update'])->name('admin.master.cash_accounts.update');
                    Route::patch('/cash-accounts/{cashAccount}/toggle', [CashAccountController::class, 'toggle'])->name('admin.master.cash_accounts.toggle');

                    Route::get('/members', [MemberController::class, 'index'])->name('admin.master.members.index');
                    Route::get('/members/create', [MemberController::class, 'create'])->name('admin.master.members.create');
                    Route::post('/members', [MemberController::class, 'store'])->name('admin.master.members.store');
                    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('admin.master.members.edit');
                    Route::put('/members/{member}', [MemberController::class, 'update'])->name('admin.master.members.update');
                    Route::patch('/members/{member}/toggle', [MemberController::class, 'toggle'])->name('admin.master.members.toggle');

                    Route::get('/saving-products', [SavingProductController::class, 'index'])->name('admin.master.saving_products.index');
                    Route::get('/saving-products/create', [SavingProductController::class, 'create'])->name('admin.master.saving_products.create');
                    Route::post('/saving-products', [SavingProductController::class, 'store'])->name('admin.master.saving_products.store');
                    Route::get('/saving-products/{savingProduct}/edit', [SavingProductController::class, 'edit'])->name('admin.master.saving_products.edit');
                    Route::put('/saving-products/{savingProduct}', [SavingProductController::class, 'update'])->name('admin.master.saving_products.update');
                    Route::patch('/saving-products/{savingProduct}/toggle', [SavingProductController::class, 'toggle'])->name('admin.master.saving_products.toggle');

                    Route::get('/loan-products', [LoanProductController::class, 'index'])->name('admin.master.loan_products.index');
                    Route::get('/loan-products/create', [LoanProductController::class, 'create'])->name('admin.master.loan_products.create');
                    Route::post('/loan-products', [LoanProductController::class, 'store'])->name('admin.master.loan_products.store');
                    Route::get('/loan-products/{loanProduct}/edit', [LoanProductController::class, 'edit'])->name('admin.master.loan_products.edit');
                    Route::put('/loan-products/{loanProduct}', [LoanProductController::class, 'update'])->name('admin.master.loan_products.update');
                    Route::patch('/loan-products/{loanProduct}/toggle', [LoanProductController::class, 'toggle'])->name('admin.master.loan_products.toggle');
                });
            Route::prefix('audit')
                ->middleware('role:SUPER_ADMIN|ADMIN|BENDAHARA|MANAGER')
                ->group(function () {
                    Route::get('/void/cash', [VoidAuditController::class, 'cash'])->name('admin.audit.void.cash');
                    Route::get('/void/savings', [VoidAuditController::class, 'savings'])->name('admin.audit.void.savings');
                    Route::get('/void/loan-payments', [VoidAuditController::class, 'loanPayments'])->name('admin.audit.void.loan_payments');
                    Route::get('/void/cash/export', [VoidAuditController::class, 'exportCash'])->name('admin.audit.void.cash.export');
                    Route::get('/void/savings/export', [VoidAuditController::class, 'exportSavings'])->name('admin.audit.void.savings.export');
                    Route::get('/void/loan-payments/export', [VoidAuditController::class, 'exportLoanPayments'])->name('admin.audit.void.loan_payments.export');
                });
            Route::prefix('reports')->middleware('role:SUPER_ADMIN|ADMIN|BENDAHARA|MANAGER')->group(function () {
                Route::get('/cash', [CashReportController::class, 'index'])->name('admin.reports.cash');
                Route::get('/savings', [SavingsReportController::class, 'index'])->name('admin.reports.savings');
                Route::get('/loans', [LoansReportController::class, 'index'])->name('admin.reports.loans');
                Route::get('/cash/export', [CashReportController::class, 'export'])->name('admin.reports.cash.export');
                Route::get('/savings/export', [SavingsReportController::class, 'export'])->name('admin.reports.savings.export');
                Route::get('/loans/export', [LoansReportController::class, 'export'])->name('admin.reports.loans.export');
            });
        });

    // Member portal (anggota)
});

require __DIR__ . '/auth.php';
