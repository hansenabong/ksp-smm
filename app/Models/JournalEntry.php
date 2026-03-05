<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'trx_date',
        'source_type',
        'source_id',
        'description',
        'created_by',
        'is_voided',
        'voided_at',
        'void_reason'
    ];

    protected $casts = [
        'is_voided' => 'boolean',
        'trx_date' => 'date',
        'voided_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
