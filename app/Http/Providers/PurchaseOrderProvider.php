<?php

namespace App\Http\Providers;

use App\Models\Stock;
use App\Models\PurchaseOrder;
use App\Events\PurchaseOrderAcceptedEvent;
use App\Http\Requests\PurchaseOrderCreateRequest;
use App\Http\Requests\PurchaseOrderUpdateRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\PurchaseOrderMessageResource;
use App\Jobs\PurchaseOrderCreateJob;

use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class PurchaseOrderProvider extends Provider
{
    private $username;

    public function __construct()
    {
        $this->username = auth()->user() ? auth()->user()->name : 'Not Available';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('purchaseorders');
        }
        $purchaseorders = Cache::remember('purchaseorders', PurchaseOrder::getCacheRemember(), function () use($request) {
            if ($request->has('status') && $request->status) {
               return PurchaseOrder::where('status', $request->status)->get();
            } else {
                return PurchaseOrder::all();
            }
        });

        return ApiResponse::success(PurchaseOrderResource::collection($purchaseorders));
    }

    /**
     *  Create a resource
     */

    public function store(PurchaseOrderCreateRequest $request)
    {
        //Issue queued job for creating purchase order
        $purchase_order_data = [
            'product_sid' => $request->product_sid,
            'fabricator_sid' => $request->fabricator_sid,
            'message' => $request->message,
            'username' => $this->username,
            'quantity' => $this->calculateQuantity($request->quantities), //Sum of all quantities
            'newDataStructure' => $this->restructureQuantity($request->quantities), // Initialize the new data structure
            'expected_at' =>$request->expected_at,
        ];

        PurchaseOrderCreateJob::dispatch($purchase_order_data);

        return ApiResponse::success(null, 'Request received check status later.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PurchaseOrderUpdateRequest $request, PurchaseOrder $purchaseorder)
    {
        try {

            // Chat
            if ($request->has('message') && !empty($request->message)) {

                $requestMsgArr = json_decode($request->message, true);

                $requestMsgArr['username'] = $this->username;

                $requestMsgArr['time'] = date('Y-m-d H:i:s');

                //Log::info($requestMsgArr);

                $messageArr = json_decode($purchaseorder->message, true);

                array_push($messageArr, $requestMsgArr);

                $purchaseorder->message = json_encode($messageArr);

                $purchaseorder->save();
            } else if ($request->has('status') && !empty($request->status)) {
                if ($request->status == 'next' && $purchaseorder->status == PurchaseOrder::STATUS[0]) {

                    // assigning next status
                    $key = array_search($purchaseorder->status, PurchaseOrder::STATUS);
                    $purchaseorder->status = PurchaseOrder::STATUS[$key + 1];
                    $purchaseorder->save();
                    //To log the status timestamp
                    $this->updateLogStatusTime($purchaseorder);

                    if ($purchaseorder->status == PurchaseOrder::STATUS[1]) {
                        PurchaseOrderAcceptedEvent::dispatch($purchaseorder);
                        //To log the status timestamp
                        $this->updateLogStatusTime($purchaseorder);
                    }

                } else if ($request->status == 'cancelled') {
                    $purchaseorder->status = $request->status;
                    $purchaseorder->save();
                }
            } else if ($request->has('quantities') && !empty($request->quantities)) {

                $purchaseorder->quantity = $this->calculateQuantity($request->quantities); //Sum of all quantities
                $purchaseorder->quantities = json_encode($this->restructureQuantity($request->quantities)); // Initialize the new data structure
                if ($request->has('expected_at') && $request->expected_at) {
                    $purchaseorder->expected_at = $request->expected_at;
                }
                $purchaseorder->save();
            }
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new PurchaseOrderResource($purchaseorder));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, PurchaseOrder $purchaseorder)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('purchaseorder' . $purchaseorder);
        }
       
        $purchaseorder = Cache::remember('purchaseorder' . $purchaseorder, PurchaseOrder::getCacheRemember(), function () use ($purchaseorder) {
            return $purchaseorder;
        });
        return ApiResponse::success(new PurchaseOrderResource($purchaseorder));
    }

    /**
     * Return only messages for this po
     */
    public function getPurchaseOrderMessage($sid)
    {
        if(!PurchaseOrder::where('sid', $sid)->exists()){
            return ApiResponse::error('Invalid request', 404);
        }
      
        $purchaseorder = PurchaseOrder::where('sid', $sid)->first();

        return ApiResponse::success(new PurchaseOrderMessageResource($purchaseorder));
    }

    /**
     * Update status
     */
    private function updateLogStatusTime($purchaseorder)
    {
        // extract old logs
        $logArr = json_decode($purchaseorder->log_status_time, true);
        
        // prepare new log
        $logNewArr = [
            'status' => $purchaseorder->status,
            'time' => date('Y-m-d H:i:s'),
        ];

        // append new in old
        array_push($logArr, $logNewArr);

        // update model
        $purchaseorder->log_status_time = json_encode($logArr);
        $purchaseorder->save();
    }

    /**
     * Sum of all quantities 
     */
    private function calculateQuantity($quantities){

        //$quantities = '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]';
        
        $quantity = 0;

        $quantities = json_decode($quantities, true);

        foreach ($quantities as $qty) {
            $quantity += array_sum($qty);
        }

        return $quantity;
    }

    /**
     * Re structure of quantities of request 
     */

    private function restructureQuantity($quantities){

       //$quantities = '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]';
        
        $quantities = json_decode($quantities, true);

        // Initialize the new data structure
        $newDataStructure = [];

        // Extract numeric parts and create the new structure
        foreach ($quantities as $item) {
            foreach ($item as $key => $value) {
                $newDataStructure[$key] = $value;
            }
        }

        return $newDataStructure;
    }
    
}
