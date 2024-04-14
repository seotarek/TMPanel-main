<?php

namespace App\Listeners;

use App\Actions\GetLinuxUser;
use App\Events\ModelHostingSubscriptionDeleting;
use App\Models\Domain;

class ModelHostingSubscriptionDeletingListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ModelHostingSubscriptionDeleting $event): void
    {
        $getLinuxUser = new GetLinuxUser();
        $getLinuxUser->setUsername($event->model->system_username);
        $getLinuxUserStatus = $getLinuxUser->handle();

        if (! empty($getLinuxUserStatus)) {
            shell_exec('userdel '.$event->model->system_username);
            shell_exec('rm -rf /home/'.$event->model->system_username);
        }
        $findRelatedDomains = Domain::where('hosting_subscription_id', $event->model->id)->get();
        if ($findRelatedDomains->count() > 0) {
            foreach ($findRelatedDomains as $domain) {
                $domain->delete();
            }
        }

    }
}
