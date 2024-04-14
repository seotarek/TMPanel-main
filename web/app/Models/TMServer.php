<?php

namespace App\Models;

use App\ApiSDK\TMApiSDK;
use App\Events\ModelTMServerCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpseclib3\Net\SSH2;

class TMServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip',
        'port',
        'username',
        'password',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            event(new ModelTMServerCreated($model));
        });

    }

    public function syncResources()
    {
        // Sync customers
        $centralServerCustomerExternalIds = [];
        $getCentralServerCustomers = Customer::where('TM_server_id', $this->id)->get();
        if ($getCentralServerCustomers->count() > 0) {
            foreach ($getCentralServerCustomers as $customer) {
                $centralServerCustomerExternalIds[] = $customer->external_id;
            }
        }

        $TMApiSDK = new TMApiSDK($this->ip, 8443, $this->username, $this->password);
        $getTMServerCustomers = $TMApiSDK->getCustomers();
        if (isset($getTMServerCustomers['data']['customers'])) {
            $TMServerCustomerIds = [];
            foreach ($getTMServerCustomers['data']['customers'] as $customer) {
                $TMServerCustomerIds[] = $customer['id'];
            }

            // Delete customers to main server that are not in external server
            foreach ($centralServerCustomerExternalIds as $centralServerCustomerExternalId) {
                if (!in_array($centralServerCustomerExternalId, $TMServerCustomerIds)) {
                    $getCustomer = Customer::where('external_id', $centralServerCustomerExternalId)
                        ->where('TM_server_id', $this->id)
                        ->first();
                    if ($getCustomer) {
                        $getCustomer->delete();
                    }
                }
            }

            // Add customers to main server from external server
            foreach ($getTMServerCustomers['data']['customers'] as $TMServerCustomer) {
                $findCustomer = Customer::where('external_id', $TMServerCustomer['id'])
                    ->where('TM_server_id', $this->id)
                    ->first();
                if (!$findCustomer) {
                    $findCustomer = new Customer();
                    $findCustomer->TM_server_id = $this->id;
                    $findCustomer->external_id = $TMServerCustomer['id'];
                }
                $findCustomer->name = $TMServerCustomer['name'];
                $findCustomer->username = $TMServerCustomer['username'];
                $findCustomer->email = $TMServerCustomer['email'];
                $findCustomer->phone = $TMServerCustomer['phone'];
                $findCustomer->address = $TMServerCustomer['address'];
                $findCustomer->city = $TMServerCustomer['city'];
                $findCustomer->state = $TMServerCustomer['state'];
                $findCustomer->zip = $TMServerCustomer['zip'];
                $findCustomer->country = $TMServerCustomer['country'];
                $findCustomer->company = $TMServerCustomer['company'];
                $findCustomer->saveQuietly();
            }
        }

        // Sync Hosting Subscriptions
        $centralServerHostingSubscriptionsExternalIds = [];
        $getCentralHostingSubscriptions = HostingSubscription::where('TM_server_id', $this->id)->get();
        if ($getCentralHostingSubscriptions->count() > 0) {
            foreach ($getCentralHostingSubscriptions as $customer) {
                $centralServerHostingSubscriptionsExternalIds[] = $customer->external_id;
            }
        }
        $getTMServerHostingSubscriptions = $TMApiSDK->getHostingSubscriptions();
        if (isset($getTMServerHostingSubscriptions['data']['HostingSubscriptions'])) {
            foreach ($getTMServerHostingSubscriptions['data']['HostingSubscriptions'] as $TMServerHostingSubscription) {

                $findHostingSubscription = HostingSubscription::where('external_id', $TMServerHostingSubscription['id'])
                    ->where('TM_server_id', $this->id)
                    ->first();
                if (!$findHostingSubscription) {
                    $findHostingSubscription = new HostingSubscription();
                    $findHostingSubscription->TM_server_id = $this->id;
                    $findHostingSubscription->external_id = $TMServerHostingSubscription['id'];
                }

                $findHostingSubscriptionCustomer = Customer::where('external_id', $TMServerHostingSubscription['customer_id'])
                    ->where('TM_server_id', $this->id)
                    ->first();
                if ($findHostingSubscriptionCustomer) {
                    $findHostingSubscription->customer_id = $findHostingSubscriptionCustomer->id;
                }

                $findHostingSubscription->system_username = $TMServerHostingSubscription['system_username'];
                $findHostingSubscription->system_password = $TMServerHostingSubscription['system_password'];

                $findHostingSubscription->domain = $TMServerHostingSubscription['domain'];
                $findHostingSubscription->save();

            }
        }


//        // Sync Hosting Plans
//        $getHostingPlans = HostingPlan::all();
//        if ($getHostingPlans->count() > 0) {
//            foreach ($getHostingPlans as $hostingPlan) {
//
//            }
//        }
    }

    public function updateServer()
    {
        $ssh = new SSH2($this->ip);
        if ($ssh->login($this->username, $this->password)) {
//
//            $output = $ssh->exec('cd /usr/local/TM/web && /usr/local/TM/php/bin/php artisan apache:ping-websites-with-curl');
//            dd($output);

            $output = '';
            $output .= $ssh->exec('wget https://raw.githubusercontent.com/seotarek/TMPanel/main/update/update-web-panel.sh -O /usr/local/TM/update/update-web-panel.sh');
            $output .= $ssh->exec('chmod +x /usr/local/TM/update/update-web-panel.sh');
            $output .= $ssh->exec('/usr/local/TM/update/update-web-panel.sh');

            dd($output);

            $this->healthCheck();
        }
    }

    public function healthCheck()
    {
        try {
            $TMApiSDK = new TMApiSDK($this->ip, 8443, $this->username, $this->password);
            $response = $TMApiSDK->healthCheck();
            if (isset($response['status']) && $response['status'] == 'ok') {
                $this->status = 'Online';
                $this->save();
            } else {
                $this->status = 'Offline';
                $this->save();
            }
        } catch (\Exception $e) {
            $this->status = 'Offline';
            $this->save();
        }

    }

}
