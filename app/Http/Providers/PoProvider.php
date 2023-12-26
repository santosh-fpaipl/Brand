<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\CheckProcurementRequest;
use App\Models\Po;
use App\Models\Order;

class PoProvider extends Provider
{
    public function checkProcurement(CheckProcurementRequest $request){

        try{

            $order = Order::where('sid', $request->order_sid)->first();

            $po = Po::where('order_id', $order->id)->whereNotNull('saleorder_id')->first();

            if(empty($po)){
                return ApiResponse::success(['status' => false]);
            } else {
                return ApiResponse::success(['status' => true]);
            }
            
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
    }
   
}
