<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $casts = [
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];
    protected $fillable = [
        'loan_id',
        'trx_date',
        'amount',
        'cash_account_id',
        'cash_transaction_id',
        'note',
        'created_by'
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
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
