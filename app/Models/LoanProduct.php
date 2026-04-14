<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    protected $fillable = [
        'name',
        'method',
        'interest_rate_monthly_bp',
        'max_tenor_months',
        'is_active',
        'penalty_daily_rate_bp',
        'early_repayment_rate_bp',
        'receivable_account_id',
        'interest_income_account_id',
        'admin_income_account_id',
        'insurance_income_account_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function receivableAccount()
    {
        return $this->belongsTo(Account::class, 'receivable_account_id');
    }

    public function interestIncomeAccount()
    {
        return $this->belongsTo(Account::class, 'interest_income_account_id');
    }

    public function adminIncomeAccount()
    {
        return $this->belongsTo(Account::class, 'admin_income_account_id');
    }

    public function insuranceIncomeAccount()
    {
        return $this->belongsTo(Account::class, 'insurance_income_account_id');
    }

    public function getInterestRateMonthlyPercentAttribute(): float
    {
        return $this->interest_rate_monthly_bp / 100.0;
    }

    /** Penalty rate per day as a decimal (e.g. 0.003 for 30bp) */
    public function getPenaltyDailyRateAttribute(): float
    {
        return ($this->penalty_daily_rate_bp ?? 30) / 10000.0;
    }

    /** Early repayment rate as a decimal (e.g. 0.05 for 500bp) */
    public function getEarlyRepaymentRateAttribute(): float
    {
        return ($this->early_repayment_rate_bp ?? 500) / 10000.0;
    }
}
