<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\DemandCreateRequest;
use App\Http\Requests\DemandUpdateRequest;
use App\Http\Resources\DemandResource;
use App\Http\Fetchers\DsFetcher;
use App\Events\ReloadDataEvent;
use Carbon\Carbon;
use App\Models\Demand;
use App\Models\DemandItem;
use App\Models\Ledger;
use App\Models\Stock;
use App\Models\Chat;
use Exception;

class DemandProvider extends Provider
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('demands');
        }
       
        $demands = Cache::remember('demands', Demand::getCacheRemember(), function () use($request) {

            $user = auth()->user();
            if ($user->isStaff()) {
                return Demand::staffDemands($user->id, $request->ledger_sid)->get();
            } elseif ($user->isFabricator()) {
                return Demand::fabricatorDemands($user->id, $request->ledger_sid)->get();
            } else {
                return Demand::managerDemands($request->status, $request->ledger_sid)->get();
            }
        });

        return ApiResponse::success(DemandResource::collection($demands));
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DemandCreateRequest $request)
    {
        // Only staff & manager can create demand
        if (!auth()->user()->isManager() && !auth()->user()->isStaff()) {
            return ApiResponse::error('Invalid request', 422);
        }

        DB::beginTransaction();
        try{

            //$quantities = '[{"green1_free":100},{"blue1_free":100}]';
            $quantities = $request->quantities;

                $ledger = Ledger::where('sid', $request->ledger_sid)->first();

                if($this->calculateQuantity($quantities) > $ledger->demandable_qty){
                    throw new Exception('Demand quantity must be less than or equal to ledger demandable quantity.');
                }

                $demand = Demand::create([
                    'sid' => Demand::generateId(),
                    'ledger_id' => $ledger->id,
                    'quantity' => $this->calculateQuantity($quantities), //Sum of all quantities
                    'expected_at' => $request->expected_at,
                    'user_id' => auth()->user()->id,
                    
                ]);

                if(!empty($demand)){


                    if($request->has('message') && !empty($request->message)){

                        Chat::create([
                            'message' => $request->message,
                            'ledger_id' => $ledger->id,
                            'sender_id' => auth()->user()->id,
                            'delivered_at' => Carbon::now(),
                        ]);
                    }

                    //balance_qty = Total(order-demand)
                    //demandable_qty = Total(ready-demand) 
                    $ledger->balance_qty = $ledger->balance_qty - $demand->quantity;
                    $ledger->demandable_qty = $ledger->demandable_qty - $demand->quantity;
                    $ledger->save();

                    $dsFetcherObj = new DsFetcher();
                    $params = '?'.$dsFetcherObj->api_secret();
                    $response = $dsFetcherObj->makeApiRequest('get', '/api/products/'.$ledger->product_sid, $params);
                    $product = $response->data;
                    if($response->status == config('api.error')){
                        throw new \Exception('#FB145 - Something went wrong, please try again later.');
                    } 

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

                            $stock = Stock::where('sku', $stock_sku)->first();

                            if(!empty($stock)){

                                DemandItem::create([

                                    'stock_id' => $stock->id,
                                    'demand_id' => $demand->id,
                                    'quantity' => $qty,

                                ]);

                            }
                        }
                    }
                }
            DB::commit();

            //To send the message to pusher
            ReloadDataEvent::dispatch(env('PUSHER_MESSAGE'));
            //End of pusher

        } catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new DemandResource($demand));
    }


     /**
     * Update the specified resource in storage.
     */
    public function update(DemandUpdateRequest $request, Demand $demand)
    {
        if(auth()->user()->isStaff()){
            if($demand->user_id != auth()->user()->id){
                return ApiResponse::error('Invalid request', 422);
            }
        } else if(auth()->user()->isFabricator()){
            return ApiResponse::error('Invalid request', 422);
        }

        try {
            if ($request->has('expected_at') && !empty($request->expected_at)) {
                $demand->expected_at = $request->expected_at;
                $demand->save();
            }
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(new DemandResource($demand));
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, Demand $demand)
    {
        if(auth()->user()->isStaff()){
            if($demand->user_id != auth()->user()->id){
                return ApiResponse::error('Invalid request', 422);
            }
        } else if(auth()->user()->isFabricator()){
            return ApiResponse::error('Invalid request', 422);
        }

        if (env('APP_DEBUG')) {
            Cache::forget('ready' . $demand);
        }
        $demand = Cache::remember('order' . $demand, Demand::getCacheRemember(), function () use ($demand) {
            return $demand;
        });
        return ApiResponse::success(new DemandResource($demand));
    }

     /**
     * Sum of all quantities 
     */
    private function calculateQuantity($quantities){

        //$quantities = '[{"green1_free":100},{"blue1_free":100}]';
        
        $quantity = 0;

        $quantities = json_decode($quantities, true);

        foreach ($quantities as $qty) {
            $quantity += array_sum($qty);
        }

        return $quantity;
    }
   
}
