<?php

namespace App\Policies;

use App\Models\LoanApplication;
use App\Models\User;

class LoanApplicationPolicy
{
    /**
     * Who can view sensitive form_data (KTP, agunan, jaminan, personal info).
     * ADMIN_DATA sees only applications they created.
     */
    public function viewSensitiveData(User $user, LoanApplication $application): bool
    {
        if ($user->hasAnyRole(['SUPER_ADMIN', 'ADMIN_DATA', 'ADMIN_COLLECTION', 'TREASURY', 'FINANCE'])) {
            return true;
        }

        return false;
    }

    /**
     * Who can approve / reject applications.
     */
    public function approve(User $user): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
