<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    protected $fillable = [
        'name',
        'method',
        'interest_rate_monthly_bp',
        'admin_fee',
        'max_tenor_months',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function getInterestRateMonthlyPercentAttribute(): float
    {
        return $this->interest_rate_monthly_bp / 100.0;
    }
}
