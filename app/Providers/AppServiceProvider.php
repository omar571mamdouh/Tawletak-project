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
    }
}
