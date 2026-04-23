<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CashAccount;
use App\Models\User;

class CashTransaction extends Model
{
    protected $casts = [
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];
    protected $fillable = [
        'code',
        'reference_code',
        'ref_number',
        'payment_method',
        'trx_date',
        'type',
        'from_cash_account_id',
        'to_cash_account_id',
        'category',
        'cash_category_id',
        'description',
        'amount',
        'created_by',
    ];

    public function fromCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'from_cash_account_id');
    }

    public function toCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'to_cash_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function scopeNotVoided($query)
    {
        return $query->where('is_voided', false);
    }
    public function voider()
    {
        return $this->belongsTo(\App\Models\User::class, 'voided_by');
    }

    public function cashCategory()
    {
        return $this->belongsTo(CashCategory::class);
    }
}
