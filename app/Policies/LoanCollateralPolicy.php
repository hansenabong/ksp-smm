<?php

namespace App\Policies;

use App\Models\LoanCollateral;
use App\Models\User;

class LoanCollateralPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN_DATA']);
    }

    public function update(User $user, LoanCollateral $collateral): bool
    {
        return $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN_DATA']);
    }

    public function release(User $user, LoanCollateral $collateral): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }

    public function delete(User $user, LoanCollateral $collateral): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
