<?php

namespace App\Models;

use App\ApiSDK\TMApiSDK;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'TM_server_id',
        'name',
        'username',
        'password',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'company',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->TM_server_id > 0) {
                $TMServer = TMServer::where('id', $model->TM_server_id)->first();
                if ($TMServer) {
                    $TMApiSDK = new TMApiSDK($TMServer->ip, 8443, $TMServer->username, $TMServer->password);
                    $createCustomer = $TMApiSDK->createCustomer([
                        'name' => $model->name,
                        'username' => $model->username,
                        'password' => $model->password,
                        'email' => $model->email,
                        'phone' => $model->phone,
                        'address' => $model->address,
                        'city' => $model->city,
                        'state' => $model->state,
                        'zip' => $model->zip,
                        'country' => $model->country,
                        'company' => $model->company,
                    ]);
                    if (isset($createCustomer['data']['customer']['id'])) {
                        $model->external_id = $createCustomer['data']['customer']['id'];
                    } else {
                        return false;
                    }

                } else {
                    return false;
                }
            }
        });

        static::deleting(function ($model) {

        });

    }

    public function hostingSubscriptions()
    {
        return $this->hasMany(HostingSubscription::class);
    }
}
