<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\User;
use App\Policies\BarangPolicy;
use App\Policies\BarangMasukPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Barang::class      => BarangPolicy::class,
        BarangMasuk::class => BarangMasukPolicy::class,
        User::class        => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Superadmin bypass Gate
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
                return true;
            }
        });

        // Dynamic Gate check against RBAC permissions
        Gate::after(function ($user, $ability, $result, $arguments) {
            if ($result !== null) {
                return $result;
            }
            if (method_exists($user, 'hasPermissionTo')) {
                return $user->hasPermissionTo($ability);
            }
            return false;
        });
    }
}
