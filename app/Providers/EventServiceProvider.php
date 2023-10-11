<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use Illuminate\Auth\Events\Verified;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\SetUpNewUserAccount;

use App\Events\PurchaseOrderAcceptedEvent;
use App\Listeners\PurchaseOrderAcceptedListener;

use App\Events\PurchaseReceivedEvent;
use App\Listeners\PurchaseReceivedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Verified::class => [
            SetUpNewUserAccount::class,
            SendWelcomeEmail::class,
        ],
        PurchaseOrderAcceptedEvent::class => [
            PurchaseOrderAcceptedListener::class,
        ],
        PurchaseReceivedEvent::class => [
            PurchaseReceivedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
