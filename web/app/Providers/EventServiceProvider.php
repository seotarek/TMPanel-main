<?php

namespace App\Providers;

use App\Events\ModelDomainCreated;
use App\Events\ModelDomainDeleting;
use App\Events\ModelHostingSubscriptionCreated;
use app\Events\ModelHostingSubscriptionCreating;
use App\Events\ModelHostingSubscriptionDeleting;
use App\Events\ModelTMServerCreated;
use App\Listeners\ModelDomainCreatedListener;
use App\Listeners\ModelDomainDeletingListener;
use App\Listeners\ModelHostingSubscriptionCreatingListener;
use App\Listeners\ModelHostingSubscriptionDeletingListener;
use App\Listeners\ModelTMServerCreatedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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
        ModelDomainDeleting::class => [
            ModelDomainDeletingListener::class,
        ],
        ModelHostingSubscriptionDeleting::class => [
            ModelHostingSubscriptionDeletingListener::class,
        ],
        ModelTMServerCreated::class => [
            ModelTMServerCreatedListener::class,
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
