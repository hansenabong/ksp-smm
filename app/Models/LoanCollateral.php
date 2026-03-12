<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanCollateral extends Model
{
    protected $fillable = [
        'loan_application_id',
        'type',
        'description',
        'estimated_value',
        'document_path',
        'document_name',
        'status',
        'released_at',
        'released_by',
        'release_note',
        'created_by',
    ];

    protected $casts = [
        'released_at'     => 'date',
        'estimated_value' => 'integer',
    ];

    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isHeld(): bool
    {
        return $this->status === 'HELD';
    }

    public static function types(): array
    {
        return ['SERTIPIKAT', 'BPKB', 'DEPOSITO', 'LAINNYA'];
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'SERTIPIKAT' => 'Sertipikat / SHM',
            'BPKB'       => 'BPKB Kendaraan',
            'DEPOSITO'   => 'Deposito',
            default      => 'Lainnya',
        };
    }
}
