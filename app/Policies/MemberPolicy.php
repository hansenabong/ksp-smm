<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    /**
     * Who can view sensitive personal data (KTP, NIK, full address, etc.)
     * Must stay in sync with Gate::define('view-sensitive-member-data') in AppServiceProvider.
     */
    public function viewSensitive(User $user, Member $member): bool
    {
        return $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN_DATA', 'ADMIN_COLLECTION']);
    }
}
