<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Redis;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Http\Fetchers\DsFetcher;
use App\Jobs\OrderUpdateJob;
use App\Events\ReloadDataEvent;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ledger;
use App\Models\Party;
use App\Models\Stock;
use App\Models\Chat;
use App\Models\Chatable;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderProvider extends Provider
{
    public function __construct()
    {
        // return $this->middleware('role:staff,manager')->except('update');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('orders');
        }
    
        $orders = Cache::remember('orders', Order::getCacheRemember(), function () use ($request) {
            $user = auth()->user();
            if ($user->isStaff()) {
                return Order::staffOrders($user->id, $request->status)->get();
            } elseif ($user->isFabricator()) {
                return Order::fabricatorOrders($user->id, $request->status)->get();
            } else {
                return Order::managerOrders($request->status)->get();
            }
        });
    
        return ApiResponse::success(OrderResource::collection($orders));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderCreateRequest $request)
    {
        
        // Only staff & manager can create order
        if (!auth()->user()->isManager() && !auth()->user()->isStaff()) {
            return ApiResponse::error('Invalid request', 422);
        }

        DB::beginTransaction();
        try{

            //$quantities = '[{"green1_free":1313},{"blue1_free":1313}]';
            $quantities = $request->quantities;
            
            $product = Cache::get($request->product_sid.date('dmy'));
            $party = Party::where('sid', $request->party_sid)->first();

            $ledger = Ledger::firstOrCreate(
                ['product_id' => $product->id, 'party_id' => $party->id],
                [
                    'sid' => Ledger::generateId(),
                    'name' => $product->name . "-" . $party->user->name,
                    'product_sid' => $product->sid,
                    'balance_qty' => 0,
                    'demandable_qty' => 0,
                    'last_activity' => 'order',
                ]);

            //Update last_activity
                if($ledger->last_activity != 'order'){
                    $ledger->last_activity = 'order';
                    $ledger->save();
                }
            //end of last_activity

            $order = Order::create([
                'sid' => Order::generateId(),
                'ledger_id' => $ledger->id,
                'quantity' => $this->calculateQuantity($quantities), //Sum of all quantities
                'expected_at' => $request->expected_at,
                'log_status_time' => Order::setLog(Order::STATUS[0]),
                'user_id' => auth()->user()->id,
            ]);
           
            $quantities_arr = json_decode($quantities,true);

            foreach($quantities_arr as $color_arr){
                foreach($color_arr as $color_size_sid => $qty){
                    $temp_arr = explode('_', $color_size_sid);
                    $color_sid = $temp_arr[0];
                    $size_sid = $temp_arr[1];

                    $color_id='';
                    $size_id='';

                    foreach($product->options as $option_obj){
                        if($option_obj->sid == $color_sid){
                            $color_id = $option_obj->id;
                            break;
                        }
                    }

                    foreach($product->ranges as $range_obj){
                        if($range_obj->sid == $size_sid){
                            $size_id = $range_obj->id;
                            break;
                        }
                    }

                    $stock_sku = $product->id.'-'.$color_id."-".$size_id;

                    Log::info($stock_sku);

                    $stock = Stock::where('sku', $stock_sku)->first();

                    if(!empty($stock)){

                        OrderItem::create([

                            'stock_id' => $stock->id,
                            'order_id' => $order->id,
                            'quantity' => $qty,

                        ]);

                    }
                }
            }
            
            if($request->has('message') && !empty($request->message)){
                $chat = Chat::create([
                    'message' => $request->message,
                    'ledger_id' => $ledger->id,
                    'sender_id' => auth()->user()->id,
                    'delivered_at' => Carbon::now(),
                ]);
                // Create the morph relationship in the chatable table
                Chatable::create([
                    'chat_id' => $chat->id,
                    'chatable_id' => $order->id,
                    'chatable_type' => Order::class,
                ]);
            }

    
            DB::commit();
            //To send the message to pusher to reload iteself
                ReloadDataEvent::dispatch(env('PUSHER_MESSAGE'));
            //End of pusher

        } catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new OrderResource($order));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        // Because he can do anything
        if (!auth()->user()->isManager()) {
            switch ($order->status) {
                case 'issued':
                    // For Accept Order (Only Fabri can accept)
                    if (!auth()->user()->isFabricator()) {
                        return ApiResponse::error('Invalid request', 422);
                    }
                    break;
                case 'accepted':
                    //For Cancel Order ()
                    if (!auth()->user()->isStaff()) {
                        return ApiResponse::error('Invalid request', 422);
                    }
                    break;
                default: 
                    return ApiResponse::error('Invalid request', 422);
                    break;
            }
        }
        
        try {
            if($order->status == 'issued' && $request->status  == 'accepted'){
                $createdAt = Carbon::parse($order->created_at);
                $twentyFourHoursLater = $createdAt->copy()->addHours(24);
                $currentDate = Carbon::now();
                if ($currentDate->gt($twentyFourHoursLater)) {
                    return ApiResponse::error('You can not accept the order after 24 hours of order created.', 404);
                } 
                OrderUpdateJob::dispatch($order);
                return ApiResponse::success(array('status' => 'pending'));

            } else {
                $order->status = $request->status;
                $order->log_status_time = Order::setLog(Order::STATUS[2], $order);
                $order->save();
            }
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new OrderResource($order));
    }

    /**
     * Display the specified resource.
     * 
     * same as index
     */
    public function show(Request $request, Order $order)
    {
        if(auth()->user()->isStaff()){
            if($order->user_id != auth()->user()->id){
                return ApiResponse::error('Invalid request', 422);
            }
        } else if(auth()->user()->isFabricator()){
            if($order->ledger->party_id != auth()->user()->party->id){
                return ApiResponse::error('Invalid request', 422);
            }
        }
        if (env('APP_DEBUG')) {
            Cache::forget('order' . $order);
        }
       
        $order = Cache::remember('order' . $order, Order::getCacheRemember(), function () use ($order) {
            return $order;
        });

       
        return ApiResponse::success(new OrderResource($order));
    }

    //Reject a order

    public function reject(Request $request, Order $order){
        // if (!auth()->user()->isFabricator()) {
        //     return ApiResponse::error('Invalid request', 422);
        // }
        try{
            if(!$order->reject){
                $order->reject = 1;
                $order->save();
            } else {
                throw new Exception('Order already rejected.');
            }
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new OrderResource($order));
    }

     /**
     * Sum of all quantities 
     */
    private function calculateQuantity($quantities){
        //$quantities = '[{"green1_free":1313},{"blue1_free":1313}]';
        $quantity = 0;
        $quantities = json_decode($quantities, true);
        foreach ($quantities as $qty) {
            $quantity += array_sum($qty);
        }
        return $quantity;
    }

   
}
