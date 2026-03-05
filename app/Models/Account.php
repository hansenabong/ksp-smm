<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
        'parent_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /** Only leaf accounts (no children) — suitable for transaction posting */
    public function scopeLeaf($q)
    {
        return $q->whereDoesntHave('children');
    }

    /** Filter by account type */
    public function scopeByType($q, string $type)
    {
        return $q->where('type', $type);
    }

    /** Only active accounts */
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
