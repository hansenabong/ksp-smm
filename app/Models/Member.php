<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_no', 'name', 'gender', 'ktp_no', 'birth_date', 'ktp_expiry',
        'marital_status', 'phone', 'address', 'city', 'is_active', 'user_id',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'birth_date' => 'date',
        'ktp_expiry' => 'date',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
