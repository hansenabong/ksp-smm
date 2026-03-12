<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    /**
     * Who can view sensitive personal data (KTP, NIK, full address, etc.)
     * BENDAHARA only sees financial data — not personal identity fields.
     */
    public function viewSensitive(User $user, Member $member): bool
    {
        return $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'PETUGAS', 'ADMIN_DATA', 'ADMIN_COLLECTION']);
    }
}
