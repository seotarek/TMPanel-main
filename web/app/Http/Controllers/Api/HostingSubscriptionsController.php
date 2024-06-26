<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\HostingSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HostingSubscriptionsController extends ApiController
{
    public function index()
    {
        $findHostingSubscriptions = HostingSubscription::all();

        return response()->json([
            'status' => 'ok',
            'message' => 'Hosting subscriptions found',
            'data' => [
                'hostingSubscriptions' => $findHostingSubscriptions,
            ],
        ]);

    }

    public function store(Request $request)
    {
        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->customer_id = $request->customer_id;
        $hostingSubscription->hosting_plan_id = $request->hosting_plan_id;
        $hostingSubscription->domain = $request->domain;
        //        $hostingSubscription->username = $request->username;
        //        $hostingSubscription->password = $request->password;
        //        $hostingSubscription->description = $request->description;
        $hostingSubscription->setup_date = Carbon::now();
        $hostingSubscription->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Hosting subscription created',
            'data' => [
                'hostingSubscription' => $hostingSubscription,
            ],
        ]);
    }

    public function destroy($id)
    {
        $findHostingSubscription = HostingSubscription::where('id', $id)->first();
        if ($findHostingSubscription) {
            $findHostingSubscription->delete();

            return response()->json([
                'status' => 'ok',
                'message' => 'Hosting subscription deleted',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Hosting subscription not found',
        ], 404);
    }

    public function update($id, Request $request)
    {
        $findHostingSubscription = HostingSubscription::where('id', $id)->first();
        if ($findHostingSubscription) {

            if (!empty($request->customer_id)) {
                $findHostingSubscription->customer_id = $request->customer_id;
            }

            $findHostingSubscription->save();

            return response()->json([
                'status' => 'ok',
                'message' => 'Hosting subscription updated',
                'data' => [
                    'hostingSubscription' => $findHostingSubscription,
                ],
            ]);
        }

    }
}
