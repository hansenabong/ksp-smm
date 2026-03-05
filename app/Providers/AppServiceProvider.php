<?php

namespace App\Providers;

use App\Models\LoanApplication;
use App\Models\Member;
use App\Policies\LoanApplicationPolicy;
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

        // Standalone gate: any role that may see personal member data
        Gate::define('view-sensitive-member-data', fn($user) =>
            $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'PETUGAS'])
        );

        // Standalone gate: user management
        Gate::define('create-user', fn($user) =>
            $user->hasAnyRole(['SUPER_ADMIN', 'ADMIN'])
        );
    }
}
