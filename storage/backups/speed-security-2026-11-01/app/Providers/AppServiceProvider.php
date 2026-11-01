<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super-admin can always open everything, so nobody can be locked out of the Roles page.
        Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null);

        // Re-check the page's permission on every Livewire action, not just the first page load.
        Livewire::addPersistentMiddleware([PermissionMiddleware::class]);
    }
}
