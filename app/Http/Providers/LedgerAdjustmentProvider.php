<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\LedgerAdjustmentCreateRequest;
use App\Http\Resources\LedgerAdjustmentResource;
use App\Models\Ledger;
use App\Models\LedgerAdjustment;
use App\Models\Order;

class LedgerAdjustmentProvider extends Provider
{
    public function store(LedgerAdjustmentCreateRequest $request){
        if(auth()->user()->isFabricator()){
            return ApiResponse::error('Invalid request', 422);
        }

        DB::beginTransaction();
        try{

            if(empty($request->order_qty)) { $order_qty = 0; } else {$order_qty = $request->order_qty;}
            if(empty($request->ready_qty)) { $ready_qty = 0; } else {$ready_qty = $request->ready_qty;}
            if(empty($request->demand_qty)) { $demand_qty = 0; } else {$demand_qty = $request->demand_qty;}

            $ledger = Ledger::where('sid', $request->ledger_sid)->first();

            if(!empty($order_qty)){
                $ledger->balance_qty = $ledger->balance_qty - $order_qty;
            }
            if(!empty($ready_qty)){
                $ledger->demandable_qty = $ledger->demandable_qty - $ready_qty;
            }
            if(!empty($demand_qty)){
                $ledger->balance_qty = $ledger->balance_qty + $demand_qty;
                $ledger->demandable_qty = $ledger->demandable_qty + $demand_qty;
            }
            $ledger->save();

            $adjustment = LedgerAdjustment::create([
                'user_id' => auth()->user()->id,
                'ledger_id' => $ledger->id,
                'order_qty' => $order_qty,
                'ready_qty' => $ready_qty,
                'demand_qty' => $demand_qty,
                'note' => $request->note,
            ]);

            DB::commit();

        } catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 404);
        }

        return ApiResponse::success(new LedgerAdjustmentResource($adjustment));
    }
   
}
