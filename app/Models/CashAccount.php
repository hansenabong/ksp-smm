<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    protected $fillable = ['name', 'type', 'is_active', 'ledger_account_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ledgerAccount()
    {
        return $this->belongsTo(\App\Models\Account::class, 'ledger_account_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
