<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositoAccount extends Model
{
    public const STATUS_ACTIVE    = 'ACTIVE';
    public const STATUS_MATURED   = 'MATURED';
    public const STATUS_WITHDRAWN = 'WITHDRAWN';
    public const STATUS_CLOSED    = 'CLOSED';

    protected $fillable = [
        'reference_code',
        'member_id',
        'depositor_name',
        'depositor_phone',
        'depositor_ktp',
        'saving_product_id',
        'principal_amount',
        'interest_rate_pct',
        'term_months',
        'start_date',
        'maturity_date',
        'status',
        'bank_account_name',
        'bank_account_no',
        'bank_name',
        'created_by',
    ];

    protected $casts = [
        'principal_amount'  => 'integer',
        'interest_rate_pct' => 'decimal:2',
        'term_months'       => 'integer',
        'start_date'        => 'date',
        'maturity_date'     => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /** Display name — member name if anggota, depositor_name if non-anggota. */
    public function depositorDisplayName(): string
    {
        return $this->member?->name ?? $this->depositor_name ?? '(Non-Anggota)';
    }

    public function isNonMember(): bool
    {
        return $this->member_id === null;
    }

    public function savingProduct()
    {
        return $this->belongsTo(SavingProduct::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(SavingTransaction::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }

    /**
     * True when the maturity date has been reached (today >= maturity_date).
     */
    public function isMatured(?\DateTimeInterface $asOf = null): bool
    {
        $asOf = $asOf ? \Illuminate\Support\Carbon::instance($asOf) : \Illuminate\Support\Carbon::today();

        return $asOf->startOfDay()->greaterThanOrEqualTo($this->maturity_date->startOfDay());
    }

    /**
     * Total simple interest payable at maturity, in IDR (rounded).
     *   bunga = principal × (rate / 100) × (term_months / 12)
     */
    public function interestAmount(): int
    {
        $rate = (float) $this->interest_rate_pct;
        $term = (int) $this->term_months;

        return (int) round($this->principal_amount * ($rate / 100) * ($term / 12));
    }

    /**
     * Total payout at maturity = principal + interest.
     */
    public function payoutAmount(): int
    {
        return (int) $this->principal_amount + $this->interestAmount();
    }

    /**
     * Days remaining until maturity (negative = matured).
     */
    public function daysToMaturity(?\DateTimeInterface $asOf = null): int
    {
        $asOf = $asOf ? \Illuminate\Support\Carbon::instance($asOf) : \Illuminate\Support\Carbon::today();

        return (int) $asOf->startOfDay()->diffInDays($this->maturity_date->startOfDay(), false);
    }

    /**
     * Display label combining status with maturity awareness.
     * ACTIVE + matured = "JATUH TEMPO"
     */
    public function displayStatus(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->isMatured()) {
            return 'JATUH TEMPO';
        }
        return $this->status;
    }
}
