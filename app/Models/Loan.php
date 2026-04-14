<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $casts = [
        'disbursed_date'      => 'date',
        'interest_monthly_percent' => 'float',
        'disbursement_fees'   => 'array',
        'disbursement_bank'   => 'array',
    ];

    protected $fillable = [
        'loan_application_id','member_id','disbursed_date','loan_product_id',
        'principal','tenor_months','method','interest_monthly_percent',
        'principal_monthly','interest_monthly','admin_monthly','insurance_monthly',
        'monthly_installment','total_payable','total_paid','outstanding',
        'status','disbursed_cash_account_id','disbursed_cash_transaction_id','created_by',
        'disbursement_fees','disbursement_bank',
    ];

    public function member() { return $this->belongsTo(Member::class); }
    public function application() { return $this->belongsTo(LoanApplication::class, 'loan_application_id'); }
    public function payments() { return $this->hasMany(LoanPayment::class); }
    public function disbursedCashAccount() { return $this->belongsTo(CashAccount::class, 'disbursed_cash_account_id'); }
    public function disbursedCashTransaction() { return $this->belongsTo(CashTransaction::class, 'disbursed_cash_transaction_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function loanProduct(){ return $this->belongsTo(LoanProduct::class, 'loan_product_id'); }
    public function collectionRecord() { return $this->hasOne(\App\Models\CollectionRecord::class); }
    public function collectionLetters() { return $this->hasMany(\App\Models\CollectionLetter::class); }
}