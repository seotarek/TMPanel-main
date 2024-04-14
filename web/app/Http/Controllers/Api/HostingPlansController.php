<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Request\CustomerCreateRequest;
use App\Http\Controllers\ApiController;
use App\Models\Customer;
use App\Models\HostingPlan;

class HostingPlansController extends ApiController
{
    /**
     * @OA\Get(
     *      path="/api/hosting-plans",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *      ),
     *
     *     @OA\PathItem (
     *     ),
     * )
     */
    public function index()
    {
        $findHostingPlans = HostingPlan::all();

        return response()->json([
            'status' => 'ok',
            'message' => 'Hosting Plans found',
            'data' => [
                'hostingPlans' => $findHostingPlans->toArray(),
            ],
        ]);

    }
}
