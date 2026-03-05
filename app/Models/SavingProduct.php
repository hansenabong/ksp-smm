<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingProduct extends Model
{
    protected $fillable = ['name', 'is_active', 'is_withdrawable', 'ledger_account_id'];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_withdrawable' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function ledgerAccount()
    {
        return $this->belongsTo(Account::class, 'ledger_account_id');
    }
}