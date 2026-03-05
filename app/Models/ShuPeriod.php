<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShuPeriod extends Model
{
    protected $casts = [
        'date_from'    => 'date',
        'date_to'      => 'date',
        'finalized_at' => 'datetime',
    ];

    protected $fillable = [
        'year',
        'date_from',
        'date_to',
        'shu_bruto',
        'cadangan_umum_bp',
        'jasa_simpanan_bp',
        'jasa_usaha_bp',
        'pengurus_bp',
        'pegawai_bp',
        'sosial_bp',
        'pendidikan_bp',
        'cadangan_umum_amount',
        'jasa_simpanan_amount',
        'jasa_usaha_amount',
        'pengurus_amount',
        'pegawai_amount',
        'sosial_amount',
        'pendidikan_amount',
        'status',
        'journal_entry_id',
        'created_by',
        'finalized_by',
        'finalized_at',
    ];

    public function memberAllocations()
    {
        return $this->hasMany(ShuMemberAllocation::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'FINALIZED';
    }

    /** Recompute derived amounts from bp values and shu_bruto. */
    public function recalculateAmounts(): void
    {
        $bruto = (int) $this->shu_bruto;
        $this->cadangan_umum_amount = (int) round($bruto * $this->cadangan_umum_bp / 10000);
        $this->jasa_simpanan_amount = (int) round($bruto * $this->jasa_simpanan_bp / 10000);
        $this->jasa_usaha_amount    = (int) round($bruto * $this->jasa_usaha_bp    / 10000);
        $this->pengurus_amount      = (int) round($bruto * $this->pengurus_bp      / 10000);
        $this->pegawai_amount       = (int) round($bruto * $this->pegawai_bp       / 10000);
        $this->sosial_amount        = (int) round($bruto * $this->sosial_bp        / 10000);
        $this->pendidikan_amount    = (int) round($bruto * $this->pendidikan_bp    / 10000);
    }
}
