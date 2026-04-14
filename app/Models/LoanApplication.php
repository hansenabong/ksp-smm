<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    protected $casts = [
        'trx_date'         => 'date',
        'form_data'        => 'array',
        'notaris_deed_date' => 'date',
    ];
    protected $fillable = [
        'trx_date','member_id','principal','tenor_months','interest_monthly_percent',
        'admin_monthly','insurance_monthly','status','notes','approval_note',
        'approved_by','approved_at','approved_cash_account_id','created_by',
        'loan_product_id', 'method', 'interest_rate_monthly_bp',
        'form_data',
        'notaris_name', 'notaris_deed_number', 'notaris_deed_date', 'notaris_city',
    ];
    public function files() { return $this->hasMany(\App\Models\LoanApplicationFile::class); }
    public function member() { return $this->belongsTo(Member::class); }
    public function loanProduct() { return $this->belongsTo(LoanProduct::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function approvedCashAccount() { return $this->belongsTo(CashAccount::class, 'approved_cash_account_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function collaterals() { return $this->hasMany(\App\Models\LoanCollateral::class); }
}