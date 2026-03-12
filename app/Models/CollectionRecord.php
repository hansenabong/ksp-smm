<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionRecord extends Model
{
    protected $fillable = [
        'loan_id', 'petugas_id', 'branch', 'escalation_level',
        'is_restruk', 'last_contact_date', 'next_contact_date', 'notes',
        'njop', 'luas_tanah', 'luas_bangunan', 'alamat_tinggal', 'wilayah',
    ];

    protected $casts = [
        'is_restruk'        => 'boolean',
        'last_contact_date' => 'date',
        'next_contact_date' => 'date',
        'escalation_level'  => 'integer',
    ];

    public const LEVELS = [
        0 => 'Normal',
        1 => 'SP I',
        2 => 'SP II',
        3 => 'SP III',
        4 => 'Somasi',
        5 => 'Pra-Cessie',
    ];

    public const LEVEL_COLORS = [
        0 => 'gray',
        1 => 'yellow',
        2 => 'orange',
        3 => 'red',
        4 => 'purple',
        5 => 'rose',
    ];

    public function loan()    { return $this->belongsTo(Loan::class); }
    public function petugas() { return $this->belongsTo(User::class, 'petugas_id'); }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVELS[$this->escalation_level] ?? 'Normal';
    }
}
