<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Table;
use App\Models\TableStatus;
use App\Observers\TableObserver;
use App\Observers\TableStatusObserver;
use App\Models\Reservation;
use App\Observers\ReservationWaitlistObserver;
use App\Observers\ReservationObserver;
use Illuminate\Support\Facades\Gate;
use Filament\Facades\Filament;

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
        Table::observe(TableObserver::class);
        TableStatus::observe(TableStatusObserver::class);
        Reservation::observe(ReservationObserver::class);
        Reservation::observe(ReservationWaitlistObserver::class);




          Gate::before(function ($user, $ability, $arguments = []) {
    if (! str_starts_with($ability, 'filament.')) {
        return null;
    }

    $policy = app(\App\Policies\GenericResourcePolicy::class);

    // شغّل before أولًا
    if (method_exists($policy, 'before')) {
        $before = $policy->before($user, $ability);
        if ($before !== null) {
            return $before; // true/false
        }
    }

    $parts = explode('.', $ability);
    if (count($parts) < 3) return null;

    [, $resourceKey, $action] = $parts;

    if (method_exists($policy, $action)) {
        return $policy->{$action}($user, $resourceKey);
    }

    return null;
});

    }
}
