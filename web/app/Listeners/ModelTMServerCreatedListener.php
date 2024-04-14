<?php

namespace App\Listeners;


use App\Events\ModelTMServerCreated;
use App\Models\TMServer;
use Illuminate\Remote\Connection;
use phpseclib3\Net\SSH2;
use Spatie\Ssh\Ssh;

class ModelTMServerCreatedListener
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
    public function handle(ModelTMServerCreated $event): void
    {
        $findTMServer =  TMServer::where('id', $event->model->id)->first();
        if (!$findTMServer) {
            return;
        }
        if ($findTMServer->status == 'installing') {
            return;
        }
        $username = $event->model->username;
        $password = $event->model->password;
        $ip = $event->model->ip;

        $ssh = new SSH2($ip);
        if ($ssh->login($username, $password)) {

            $ssh->exec('wget https://raw.githubusercontent.com/seotarek/TMPanel/main/installers/install.sh');
            $ssh->exec('chmod +x install.sh');
            $ssh->exec('./install.sh  >TM-install.log 2>&1 </dev/null &');

            $findTMServer->status = 'installing';
            $findTMServer->save();

        } else {
            $findTMServer->status = 'can\'t connect to server';
            $findTMServer->save();
        }
    }
}
