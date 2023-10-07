<?php
namespace App\Http\Providers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use App\Models\Purchase;
use App\Http\Requests\PurchaseCreateRequest;
use App\Http\Requests\PurchaseUpdateRequest;
use App\Http\Resources\PurchaseResource;
use App\Http\Resources\PurchaseMessageResource;
use App\Events\PurchaseReceivedEvent;
use App\Models\JobWorkOrder;
use Illuminate\Support\Facades\Http;
use Exception;
use Carbon\Carbon;

class PurchaseProvider extends Provider
{
    private $username;

    public function __construct()
    {
        $this->username = auth()->user() ? auth()->user()->name : 'Test 111';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Cache::forget('purchases');
        $purchases = Cache::remember('purchases', 24 * 60 * 60, function () use($request) {
            if ($request->has('status') && $request->status) {
                return Purchase::where('status', $request->status)->get();
            } else {
                return Purchase::get();
            }
        });
        return ApiResponse::success(PurchaseResource::collection($purchases));
    }

    /**
     *  Create a resource
     */

    public function store(PurchaseCreateRequest $request){
        
        $quantity = 0;

        //$originalData = '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]';
        
        $quantities = json_decode($request->quantities, true);

        foreach($quantities as $qty){
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

        try{
            $purchase = '';

            $jobWorkOrder = JobWorkOrder::where('sid', $request->job_work_order_sid)->first();


            $requestMsgArr = json_decode($request->message, true);

            $requestMsgArr['username'] = $this->username;

            $requestMsgArr['time'] = date('Y-m-d H:i:s');

            //Log::info($requestMsgArr);

            $messageArr = json_decode($jobWorkOrder->message, true);

            array_push($messageArr, $requestMsgArr);

            $purchase = Purchase::create([
                'job_work_order_id' => $jobWorkOrder->id,
                'job_work_order_sid' => $jobWorkOrder->sid,
                'product_id' => $jobWorkOrder->product_id,
                'product_sid' => $jobWorkOrder->product_sid,
                'fabricator_id' => $jobWorkOrder->fabricator_id,
                'fabricator_sid' => $jobWorkOrder->fabricator_sid,
                'sid' => Purchase::generateId(),
                'invoice_no' => $request->invoice_no,
                'invoice_date' => $request->invoice_date,
                'quantity' => $quantity,
                'quantities' => json_encode($newDataStructure),
                'message' => json_encode($messageArr),
                'log_status_time' => $jobWorkOrder->log_status_time,
            ]);

            //To log the status timestamp
            $this->updateLogStatusTime(Purchase::find($purchase->id));

        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new PurchaseResource($purchase));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PurchaseUpdateRequest $request, Purchase $purchase)
    {
        try{

            // Chat
            if ($request->has('message') && $request->message) {

                $requestMsgArr = json_decode($request->message, true);

                $requestMsgArr['username'] = $this->username;

                $requestMsgArr['time'] = date('Y-m-d H:i:s');

                //Log::info($requestMsgArr);

                $messageArr = json_decode($purchase->message, true);

                array_push($messageArr, $requestMsgArr);

                $purchase->message = json_encode($messageArr);

                $purchase->save();

            } 

            //To update quantities

            if($request->has('quantities') && $request->quantities){

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

                $purchase->quantity = $quantity;
                $purchase->quantities = json_encode($newDataStructure);
                
                $purchase->save();
            } 

            //To update status

            if($request->has('status') && $request->status){


                if ($request->status == 'next' && $purchase->status != Purchase::FINAL_STATUS) 
                {
                    // assigning next status
                    $key = array_search($purchase->status, Purchase::STATUS);
                    $purchase->status = Purchase::STATUS[$key + 1];
                    $purchase->save();

                    //To log the status timestamp
                    $this->updateLogStatusTime($purchase);

                    if($purchase->status == Purchase::FINAL_STATUS){
                        $complete_time = '';
                        $logArr = json_decode($purchase->log_status_time, true);
                        foreach($logArr as $arr)
                        {
                            if($arr['status'] == Purchase::FINAL_STATUS){
                                $time_arr = explode(" ", $arr['time']);
                                $complete_time = $time_arr[0];
                            }
                        }

                        $expected_at_time = Carbon::parse($purchase->jobWorkOrder->expected_at);
                        $completed_at_time = Carbon::parse($complete_time);

                        // Add a positive or negative sign based on the difference
                        $sign = $expected_at_time->gt($completed_at_time) ? '+' : '-';

                        $time_difference = $sign.$expected_at_time->diffInDays($completed_at_time);

                        $loss_quantity =  $purchase->quantity - $purchase->jobWorkOrder->quantity;

                        $loss_quantities = [];

                        $jwo_quantities = json_decode($purchase->jobWorkOrder->quantities, true);

                        foreach(json_decode($purchase->quantities, true) as $key => $value){
                            $loss_quantities[$key] = $value - $jwo_quantities[$key];
                        }

                        $purchase->time_difference = $time_difference;
                        $purchase->loss_quantity = $loss_quantity;
                        $purchase->loss_quantities = json_encode($loss_quantities);
                        $purchase->save();


                    }
                } else if ($request->status == 'cancelled') {
                    $purchase->status = $request->status;
                    $purchase->save();
                }

               if($purchase->status == Purchase::FINAL_STATUS){
                    PurchaseReceivedEvent::dispatch($purchase);
               }
              
            } 

        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new PurchaseResource($purchase));
    }

     /**
     * Display the specified resource.
     */
    public function show(Request $request, Purchase $purchase)
    {
        Cache::forget('purchase'.$purchase);
        $purchase = Cache::remember('purchase'.$purchase, 24 * 60 * 60, function () use($purchase) {
            return $purchase;
        });
        return ApiResponse::success(new PurchaseResource($purchase));
    }

    public function getPurchaseMessage($sid)
    {
        if(!Purchase::where('sid', $sid)->exists()){
            return ApiResponse::error('Purchase does not exist.', 404);
        }
        Cache::forget('purchase_message' . $sid);
        $purchase = Cache::remember('purchase_message' . $sid, 24 * 60 * 60, function () use($sid) {
            return Purchase::where('sid', $sid)->first();
        });

        return ApiResponse::success(new PurchaseMessageResource($purchase));
    }

    private function updateLogStatusTime($purchase){

        $logNewArr = [];

        $logArr = json_decode($purchase->log_status_time, true);

        $logNewArr['status'] = $purchase->status;

        $logNewArr['time'] = date('Y-m-d H:i:s');

        array_push($logArr, $logNewArr);

        $purchase->log_status_time = json_encode($logArr);

        $purchase->save();

    }
    
}