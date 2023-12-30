<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\AdjustmentRequest;
use App\Http\Resources\AdjustmentResource;
use App\Http\Fetchers\DsFetcher;
use App\Models\Ledger;
use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Stock;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use App\Models\Chat;
use App\Models\Chatable;
use Carbon\Carbon;

class AdjustmentProvider extends Provider
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('adjustments');
        }
    
        $adjustments = Cache::remember('adjustments', Adjustment::getCacheRemember(), function () use ($request) {
            $user = auth()->user();
            if ($user->isStaff()) {
                return Adjustment::staffAdjustments($user->id)->get();
            } elseif ($user->isFabricator()) {
                return Adjustment::fabricatorAdjustments($user->id)->get();
            } else {
                return Adjustment::managerAdjustments()->get();
            }
        });
    
        return ApiResponse::success(AdjustmentResource::collection($adjustments));
    }

    public function store(AdjustmentRequest $request)
    {
        if(auth()->user()->isFabricator()){
            return ApiResponse::error('Invalid request', 422);
        }

        DB::beginTransaction();
        try{

            //$quantities = '[{"green1_free":1313},{"blue1_free":1313}]';
            $quantities = $request->quantities;

            $quantity = $this->calculateQuantity($quantities);

            $product = Cache::get($request->product_sid.date('dmy'));

            $ledger = Ledger::where('sid', $request->ledger_sid)->first();

            switch ($request->type) {
                case 'order':
                    $ledger->balance_qty = $ledger->balance_qty - $quantity;
                    $ledger->last_activity = 'order';
                    break;
                case 'ready':
                    $ledger->demandable_qty = $ledger->demandable_qty - $quantity;
                    $ledger->last_activity = 'ready';
                case 'demand':
                    $ledger->balance_qty = $ledger->balance_qty + $quantity;
                    $ledger->demandable_qty = $ledger->demandable_qty + $quantity;
                    $ledger->last_activity = 'demand';
                default: break;
            }

            $ledger->save();

            $adjustment = Adjustment::create([
                'sid' => Adjustment::generateId(),
                'type' => $request->type,
                'ledger_id' => $ledger->id,
                'quantity' => $this->calculateQuantity($quantities), //Sum of all quantities
                'user_id' => auth()->user()->id,
            ]);

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

                        AdjustmentItem::create([
                            'stock_id' => $stock->id,
                            'adjustment_id' => $adjustment->id,
                            'quantity' => $qty,
                        ]);

                    }
                }
            }

            if($request->has('note') && !empty($request->note)){

                $chat = Chat::create([
                    'message' => $request->note,
                    'ledger_id' => $ledger->id,
                    'sender_id' => auth()->user()->id,
                    'delivered_at' => Carbon::now(),
                ]);
                // Create the morph relationship in the chatable table
                Chatable::create([
                    'chat_id' => $chat->id,
                    'chatable_id' => $adjustment->id,
                    'chatable_type' => Adjustment::class,
                ]);
            }
         
            DB::commit();

        } catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 404);
        }

        return ApiResponse::success(new AdjustmentResource($adjustment));
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
