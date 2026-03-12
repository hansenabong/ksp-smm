<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionLetter extends Model
{
    protected $fillable = [
        'loan_id', 'letter_type', 'letter_number', 'letter_date', 'created_by', 'notes',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];

    public const TYPES = [
        'SP1'    => 'Surat Peringatan I',
        'SP2'    => 'Surat Peringatan II',
        'SP3'    => 'Surat Peringatan III',
        'SOMASI' => 'Somasi',
        'CESSIE' => 'Pra-Cessie',
    ];

    // Map letter type → escalation level
    public const TYPE_LEVEL = [
        'SP1'    => 1,
        'SP2'    => 2,
        'SP3'    => 3,
        'SOMASI' => 4,
        'CESSIE' => 5,
    ];

    public function loan()    { return $this->belongsTo(Loan::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->letter_type] ?? $this->letter_type;
    }
}
