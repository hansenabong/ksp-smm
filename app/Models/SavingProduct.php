<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingProduct extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'is_withdrawable',
        'ledger_account_id',
        'is_deposito',
        'default_interest_rate_pct',
        'default_term_months',
    ];

    protected $casts = [
        'is_active'                 => 'boolean',
        'is_withdrawable'           => 'boolean',
        'is_deposito'               => 'boolean',
        'default_interest_rate_pct' => 'decimal:2',
        'default_term_months'       => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function ledgerAccount()
    {
        return $this->belongsTo(Account::class, 'ledger_account_id');
    }

    public function depositoAccounts()
    {
        return $this->hasMany(DepositoAccount::class);
    }
}
