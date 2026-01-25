<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            activity()
                ->useLog('auth')
                ->causedBy($event->user)
                ->withProperties([
                    'guard' => $event->guard,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('user logged in');
        });

        Event::listen(Logout::class, function (Logout $event) {
            activity()
                ->useLog('auth')
                ->causedBy($event->user)
                ->withProperties([
                    'guard' => $event->guard,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('user logged out');
        });

        Event::listen(Failed::class, function (Failed $event) {
            activity()
                ->useLog('auth')
                ->withProperties([
                    'guard' => $event->guard,
                    'email' => $event->credentials['email'] ?? null,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('failed login attempt');
        });
    }
}
