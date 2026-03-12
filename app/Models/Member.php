<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['member_no', 'name', 'phone', 'address', 'is_active', 'user_id'];

    protected $casts = [
        'is_active' => 'boolean',
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
