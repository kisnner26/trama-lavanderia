<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('receive', fn (User $user, Membership $membership): bool => $membership->user_id === $user->id && $membership->role->canReceive());
        Gate::define('manage-catalog', fn (User $user, Membership $membership): bool => $membership->user_id === $user->id && $membership->role === Role::Owner);
    }
}
