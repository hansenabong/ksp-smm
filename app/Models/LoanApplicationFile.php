<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplicationFile extends Model
{
    protected $fillable = [
        'loan_application_id',
        'category',
        'file_path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    public function application()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}