<?php

namespace App\Http\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Providers\Provider;
use App\Http\Fetchers\DsFetcher;
use App\Http\Responses\ApiResponse;
use App\Models\Stock;
use App\Http\Resources\StockResourcePartId;
use App\Http\Resources\StockResource;
use App\Http\Resources\ShowProductResource;
use App\Http\Resources\StockSkuResource;
use App\Http\Requests\StockRequest;
use App\Http\Resources\productResource;
use Exception;
use App\Models\Ledger;
use App\Models\Party;
use App\Models\Order;

class FabStockProvider extends Provider
{
   

    public function newOrderStock($partySid)
    {
        $party = Party::where('sid', $partySid)->first();


        $latest_order_each_ledger = DB::table('orders')
            ->join('ledgers', 'orders.ledger_id', '=', 'ledgers.id')
            ->select('orders.*','ledgers.balance_qty','ledgers.product_sid')
            ->where('ledgers.party_id', $party->id)
            ->whereIn('orders.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('orders')
                    ->groupBy('ledger_id');
            })
        ->orderBy('orders.id', 'desc')
        ->get();

        $stockResponse = [];

        foreach($latest_order_each_ledger as $order){

            $dsFetcherObj = new DsFetcher();
            $params = '?'.$dsFetcherObj->api_secret();
            $response = $dsFetcherObj->makeApiRequest('get', '/api/products/'.$order->product_sid, $params);
            $product = $response->data;

            $stock = Stock::where('product_sid', $order->product_sid)->where('active', true)->exists();

            array_push($stockResponse, [
                'stock' => $order->balance_qty,
                'active' => $stock,
                'product' => new productResource($product),
            ]);
        }
        return ApiResponse::success($stockResponse);
        
    }

    
}
