<?php

namespace App\Listeners;

use App\Actions\ApacheWebsiteDelete;
use App\Events\ModelDomainDeleting;
use App\ShellApi;

class ModelDomainDeletingListener
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
    public function handle(ModelDomainDeleting $event): void
    {
        $domainRoot = '/home/'.$event->model->domain_username.'/domains/'.$event->model->domain;

        ShellApi::exec('rm -rf '.$domainRoot);

        $deleteApacheWebsite = new ApacheWebsiteDelete();
        $deleteApacheWebsite->setDomain($event->model->domain);
        $deleteApacheWebsite->handle();
    }
}
