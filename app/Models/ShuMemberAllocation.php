<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShuMemberAllocation extends Model
{
    protected $fillable = [
        'shu_period_id',
        'member_id',
        'avg_savings',
        'total_loan_payments',
        'shu_simpanan',
        'shu_usaha',
        'shu_total',
    ];

    public function shuPeriod()
    {
        return $this->belongsTo(ShuPeriod::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
