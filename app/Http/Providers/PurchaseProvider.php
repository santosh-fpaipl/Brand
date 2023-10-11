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
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Http;
use Exception;
use Carbon\Carbon;

class PurchaseProvider extends Provider
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
        Cache::forget('purchases');
        $purchases = Cache::remember('purchases', Purchase::getCacheRemember(), function () use($request) {
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

        $quantities = '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]';
        
        $quantities = json_decode($quantities, true);

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

            $purchaseOrder = PurchaseOrder::where('sid', $request->purchase_order_sid)->first();


            $requestMsgArr = json_decode($request->message, true);

            $requestMsgArr['username'] = $this->username;

            $requestMsgArr['time'] = date('Y-m-d H:i:s');

            //Log::info($requestMsgArr);

            $messageArr = json_decode($purchaseOrder->message, true);

            array_push($messageArr, $requestMsgArr);

            $purchase = Purchase::create([
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_sid' => $purchaseOrder->sid,
                'product_id' => $purchaseOrder->product_id,
                'product_sid' => $purchaseOrder->product_sid,
                'fabricator_id' => $purchaseOrder->fabricator_id,
                'fabricator_sid' => $purchaseOrder->fabricator_sid,
                'sid' => Purchase::generateId(),
                'invoice_no' => $request->invoice_no,
                'invoice_date' => $request->invoice_date,
                'quantity' => $this->calculateQuantity($request->quantities), //Sum of all quantities
                'quantities' => json_encode($this->restructureQuantity($request->quantities)), // Initialize the new data structure
                'message' => json_encode($messageArr),
                'log_status_time' => $purchaseOrder->log_status_time,
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
            if ($request->has('message') && !empty($request->message)) {

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

            if($request->has('quantities') && !empty($request->quantities)){

                $purchase->quantity = $this->calculateQuantity($request->quantities); //Sum of all quantities
                $purchase->quantities = json_encode($this->restructureQuantity($request->quantities)); // Initialize the new data structure
                
                $purchase->save();
            } 

            //To update status

            if($request->has('status') && !empty($request->status)){


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

                        $expected_at_time = Carbon::parse($purchase->purchaseOrder->expected_at);
                        $completed_at_time = Carbon::parse($complete_time);

                        // Add a positive or negative sign based on the difference
                        $sign = $expected_at_time->gt($completed_at_time) ? '+' : '-';

                        $time_difference = $sign.$expected_at_time->diffInDays($completed_at_time);

                        $loss_quantity =  $purchase->quantity - $purchase->purchaseOrder->quantity;

                        $loss_quantities = [];

                        $jwo_quantities = json_decode($purchase->purchaseOrder->quantities, true);

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
        $purchase = Cache::remember('purchase'.$purchase, Purchase::getCacheRemember(), function () use($purchase) {
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
        $purchase = Cache::remember('purchase_message' . $sid, Purchase::getCacheRemember(), function () use($sid) {
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

     /**
     * Sum of all quantities 
     */
    private function calculateQuantity($quantities){

        $quantity = 0;

        //$originalData = '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]';

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