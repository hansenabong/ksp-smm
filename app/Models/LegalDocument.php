<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalDocument extends Model
{
    protected $fillable = [
        'legal_berkas_id',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(LegalBerkas::class, 'legal_berkas_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}
