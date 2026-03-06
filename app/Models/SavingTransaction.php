<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingTransaction extends Model
{
    protected $casts = [
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];
    protected $fillable = [
        'reference_code',
        'trx_date',
        'type',
        'amount',
        'member_id',
        'saving_product_id',
        'cash_account_id',
        'cash_transaction_id',
        'description',
        'created_by',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    public function savingProduct()
    {
        return $this->belongsTo(SavingProduct::class);
    }
    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class);
    }
    public function cashTransaction()
    {
        return $this->belongsTo(CashTransaction::class);
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
}
