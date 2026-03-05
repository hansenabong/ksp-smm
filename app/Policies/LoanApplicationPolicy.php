<?php

namespace App\Policies;

use App\Models\LoanApplication;
use App\Models\User;

class LoanApplicationPolicy
{
    /**
     * Who can view sensitive form_data (KTP, agunan, jaminan, personal info).
     * BENDAHARA sees only financial summary; PETUGAS sees only applications they created.
     */
    public function viewSensitiveData(User $user, LoanApplication $application): bool
    {
        if ($user->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER'])) {
            return true;
        }

        if ($user->hasRole('PETUGAS')) {
            return $application->created_by === $user->id;
        }

        return false; // BENDAHARA
    }

    /**
     * Who can approve / reject applications.
     */
    public function approve(User $user): bool
    {
        return $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER']);
    }
}
