<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalBerkas extends Model
{
    protected $table = 'legal_berkas';

    protected $fillable = [
        'title',
        'description',
        'loan_application_id',
        'member_id',
        'created_by',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(LegalDocument::class);
    }

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
