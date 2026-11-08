<?php

namespace App\Providers;

use App\Http\Middleware\EnsureActiveUser;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::define('module-view', fn (User $user, string $module): bool => $user->hasPermission($module.':view'));
        Gate::define('module-manage', fn (User $user, string $module): bool => $user->hasPermission($module.':manage'));
        Gate::define('module-approve', fn (User $user, string $module): bool => $user->hasPermission($module.':approve'));
        Livewire::addPersistentMiddleware([EnsureActiveUser::class]);
    }
}
