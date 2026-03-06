<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $casts = [
        'disbursed_date'      => 'date',
        'interest_monthly_percent' => 'float',
        'disbursement_fees'   => 'array',
    ];

    protected $fillable = [
        'loan_application_id','member_id','disbursed_date','loan_product_id',
        'principal','tenor_months','interest_monthly_percent',
        'principal_monthly','interest_monthly','admin_monthly','insurance_monthly',
        'monthly_installment','total_payable','total_paid','outstanding',
        'status','disbursed_cash_account_id','disbursed_cash_transaction_id','created_by',
        'disbursement_fees',
    ];

    public function member() { return $this->belongsTo(Member::class); }
    public function application() { return $this->belongsTo(LoanApplication::class, 'loan_application_id'); }
    public function payments() { return $this->hasMany(LoanPayment::class); }
    public function disbursedCashAccount() { return $this->belongsTo(CashAccount::class, 'disbursed_cash_account_id'); }
    public function disbursedCashTransaction() { return $this->belongsTo(CashTransaction::class, 'disbursed_cash_transaction_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function loanProduct(){ return $this->belongsTo(LoanProduct::class, 'loan_product_id'); }
    }