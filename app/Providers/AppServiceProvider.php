<?php

namespace App\Providers;

use App\Models\LoanApplication;
use App\Models\LoanCollateral;
use App\Models\Member;
use App\Policies\LoanApplicationPolicy;
use App\Policies\LoanCollateralPolicy;
use App\Policies\MemberPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Model policies
        Gate::policy(Member::class, MemberPolicy::class);
        Gate::policy(LoanApplication::class, LoanApplicationPolicy::class);
        Gate::policy(LoanCollateral::class, LoanCollateralPolicy::class);

        // Standalone gate: any role that may see personal member data
        Gate::define('view-sensitive-member-data', fn($user) =>
            $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN_DATA', 'ADMIN_COLLECTION'])
        );

        // Standalone gate: user management
        Gate::define('create-user', fn($user) =>
            $user->hasRole('SUPER_ADMIN')
        );
    }
}
