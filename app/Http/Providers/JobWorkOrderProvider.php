<?php

namespace App\Http\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use App\Models\JobWorkOrder;
use App\Http\Requests\JobWorkOrderRequest;
use App\Http\Requests\JobWorkOrderUpdateRequest;
use App\Http\Resources\JobWorkOrderResource;
use App\Http\Resources\JobWorkOrderMessageResource;
use App\Events\JobWorkOrderAcceptedEvent;
use App\Models\Stock;
use Exception;
use App\Http\Responses\JsonResponse;

class JobWorkOrderProvider extends Provider
{
    private $username;

    public function __construct()
    {
        $this->username = auth()->user() ? auth()->user()->name : 'Test';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Cache::forget('jobworkorders');
        $jobworkorders = Cache::remember('jobworkorders', 24 * 60 * 60, function () use($request) {
            if ($request->has('status') && $request->status) {
               return JobWorkOrder::where('status', $request->status)->get();
            } else {
                return JobWorkOrder::get();
            }
        });

        return ApiResponse::success(JobWorkOrderResource::collection($jobworkorders));
    }

    /**
     *  Create a resource
     */

    public function store(JobWorkOrderRequest $request)
    {
        $jwo = '';
        $quantity = 0;
        //$originalData = '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]';
        $quantities = json_decode($request->quantities, true);

        foreach ($quantities as $qty) {
            $quantity += array_sum($qty);
        }

        // Initialize the new data structure
        $newDataStructure = [];

        // Extract numeric parts and create the new structure
        foreach ($quantities as $item) {
            foreach ($item as $key => $value) {
                $newDataStructure[$key] = $value;
            }
        }

        try {

            //Jobworkorder can be placed of stock in draft
            //but not if it's not exits.
            
            $stock = Stock::where('product_sid', $request->product_sid)->where('active',1)->exists();
            
            if(!$stock){
                throw new Exception('Stock does not exit.');
            }

            $fabricator = Http::get(env('FABRICATOR_APP').'/api/internal/fabricators/' . $request->fabricator_sid);

            if($fabricator['status'] == config('api.error')){
                throw new Exception('Fabricator does not exit.');
            } 

            $product = Http::get(env('DS_APP').'/api/internal/products/' . $request->product_sid);

            
            if($product['status'] == config('api.error')){
                throw new Exception('Product does not exit.');
            } 

            $messageArr = [['title' => 'Purchase Order placed', 'body' => $request->message, 'username' => $this->username, 'time' => date('Y-m-d H:i:s')]];


            $jwo = JobWorkOrder::create([
                'product_id' => $product['data']['id'],
                'product_sid' => $request->product_sid,
                'fabricator_id' => $fabricator['data']['id'],
                'fabricator_sid' => $request->fabricator_sid,
                'sid' => 'JWO-' . time() . '-' . $product['data']['id'],
                'quantity' => $quantity,
                'quantities' => json_encode($newDataStructure),
                'expected_at' => $request->delivery_date,
                'message' => json_encode($messageArr),
            ]);
            
            //To log the status timestamp
            $this->updateLogStatusTime($jwo);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }

        return ApiResponse::success(new JobWorkOrderResource($jwo));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobWorkOrderUpdateRequest $request, JobWorkOrder $jobworkorder)
    {
        try {

            // Chat
            if ($request->has('message') && $request->message) {

                $requestMsgArr = json_decode($request->message, true);

                $requestMsgArr['username'] = $this->username;

                $requestMsgArr['time'] = date('Y-m-d H:i:s');

                //Log::info($requestMsgArr);

                $messageArr = json_decode($jobworkorder->message, true);

                array_push($messageArr, $requestMsgArr);

                $jobworkorder->message = json_encode($messageArr);

                $jobworkorder->save();
            } else if ($request->has('status') && $request->status) {

                if ($request->status == 'next' && $jobworkorder->status == JobWorkOrder::STATUS[0]) {

                    // assigning next status
                    $key = array_search($jobworkorder->status, JobWorkOrder::STATUS);
                    $jobworkorder->status = JobWorkOrder::STATUS[$key + 1];
                    $jobworkorder->save();
                    //To log the status timestamp
                    $this->updateLogStatusTime($jobworkorder);
                } else if ($request->status == 'cancelled') {
                    $jobworkorder->status = $request->status;
                    $jobworkorder->save();
                }

                if ($jobworkorder->status == JobWorkOrder::STATUS[1]) {
                    JobWorkOrderAcceptedEvent::dispatch($jobworkorder);
                    //To log the status timestamp
                    $this->updateLogStatusTime($jobworkorder);
                }

                
                
            } else if ($request->has('quantities') && $request->quantities) {


                $quantity = 0;
                //$originalData = '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]';

                $quantities = json_decode($request->quantities, true);

                foreach ($quantities as $qty) {
                    $quantity += array_sum($qty);
                }

                // Initialize the new data structure
                $newDataStructure = [];

                // Extract numeric parts and create the new structure
                foreach ($quantities as $item) {
                    foreach ($item as $key => $value) {
                        $newDataStructure[$key] = $value;
                    }
                }

                $jobworkorder->quantity = $quantity;
                $jobworkorder->quantities = json_encode($newDataStructure);
                if ($request->has('expected_at') && $request->expected_at) {
                    $jobworkorder->expected_at = $quantity;
                }
                $jobworkorder->save();
            }
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new JobWorkOrderResource($jobworkorder));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, JobWorkOrder $jobworkorder)
    {
        Cache::forget('jobworkorder' . $jobworkorder);
        $jobworkorder = Cache::remember('jobworkorder' . $jobworkorder, 24 * 60 * 60, function () use ($jobworkorder) {
            return $jobworkorder;
        });
        return ApiResponse::success(new JobWorkOrderResource($jobworkorder));
    }



    public function getJobWorkOrderMessage($sid)
    {
        if(!JobWorkOrder::where('sid', $sid)->exists()){
            return ApiResponse::error('Jobworkorder does not exist.', 404);
        }
        Cache::forget('jobworkorder_message' . $sid);
        $jobworkorder = Cache::remember('jobworkorder_message' . $sid, 24 * 60 * 60, function () use($sid) {
            return JobWorkOrder::where('sid', $sid)->first();
        });
        return ApiResponse::success(new JobWorkOrderMessageResource($jobworkorder));
    }

    private function updateLogStatusTime($jobworkorder){

        $logNewArr = [];

        if(empty($jobworkorder->log_status_time)){

            $logArr = [];
            $jobworkorder = JobWorkOrder::find($jobworkorder->id);

        } else {
            $logArr = json_decode($jobworkorder->log_status_time, true);
        }

        $logNewArr['status'] = $jobworkorder->status;

        $logNewArr['time'] = date('Y-m-d H:i:s');

        array_push($logArr, $logNewArr);

        $jobworkorder->log_status_time = json_encode($logArr);

        $jobworkorder->save();

    }
    
}
