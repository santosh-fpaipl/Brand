<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ReadyCreateRequest;
use App\Http\Resources\ReadyResource;
use App\Http\Fetchers\DsFetcher;
use App\Events\ReloadDataEvent;
use Carbon\Carbon;
use App\Models\Ready;
use App\Models\ReadyItem;
use App\Models\Ledger;
use App\Models\Stock;
use App\Models\Chat;
use Exception;

class ReadyProvider extends Provider
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (env('APP_DEBUG')) {
            Cache::forget('readies');
        }
       
        $readies = Cache::remember('readies', Ready::getCacheRemember(), function () use($request) {

            $user = auth()->user();
            if ($user->isStaff()) {
                return Ready::staffRedies($user->id, $request->ledger_sid)->get();
            } elseif ($user->isFabricator()) {
                return Ready::fabricatorRedies($user->id, $request->ledger_sid)->get();
            } else {
                return Ready::managerRedies($request->ledger_sid)->get();
            }
            
            return Ready::orderBy('created_at', 'desc')->get(); // take(5)->
        });

        return ApiResponse::success(ReadyResource::collection($readies));
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReadyCreateRequest $request)
    {
        // Only staff & manager can create order
        if (!auth()->user()->isManager() && !auth()->user()->isFabricator()) {
            return ApiResponse::error('Invalid request', 422);
        }

        DB::beginTransaction();
        try{

            //$quantities = '[{"green1_free":200},{"blue1_free":200}]';
            $quantities = $request->quantities;

                $ledger = Ledger::where('sid', $request->ledger_sid)->first();

                if($this->calculateQuantity($quantities) > $ledger->balance_qty){
                    throw new Exception('Ready quantity must be less than or equal to ledger balance quantity.');
                }

                $ready = Ready::create([
                    'sid' => Ready::generateId(),
                    'ledger_id' => $ledger->id,
                    'quantity' => $this->calculateQuantity($quantities), //Sum of all quantities
                    'user_id' => auth()->user()->id,
                    
                ]);

                if(!empty($ready)){


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
                    $ledger->demandable_qty = $ledger->demandable_qty + $ready->quantity;
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

                                ReadyItem::create([

                                    'stock_id' => $stock->id,
                                    'ready_id' => $ready->id,
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
        return ApiResponse::success(new ReadyResource($ready));
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, Ready $ready)
    {
        if(auth()->user()->isStaff()){
            if($ready->ledger->order->user_id != auth()->user()->id){
                return ApiResponse::error('Invalid request', 422);
            }
        } else if(auth()->user()->isFabricator()){
            if($ready->user_id != auth()->user()->id){
                return ApiResponse::error('Invalid request', 422);
            }
        }

        if (env('APP_DEBUG')) {
            Cache::forget('ready' . $ready);
        }
        $ready = Cache::remember('order' . $ready, Ready::getCacheRemember(), function () use ($ready) {
            return $ready;
        });
        return ApiResponse::success(new ReadyResource($ready));
    }

     /**
     * Sum of all quantities 
     */
    private function calculateQuantity($quantities){

        //$quantities = '[{"green1_free":200},{"blue1_free":200}]';
        
        $quantity = 0;

        $quantities = json_decode($quantities, true);

        foreach ($quantities as $qty) {
            $quantity += array_sum($qty);
        }

        return $quantity;
    }
   
}
