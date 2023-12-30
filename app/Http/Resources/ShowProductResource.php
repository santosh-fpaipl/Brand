<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Fetchers\DsFetcher;
use App\Http\Resources\StockLedgerResource;
use App\Http\Resources\StockProductResource;
use App\Models\Ledger;
class ShowProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dsFetcherObj = new DsFetcher();
        $params = '?'.$dsFetcherObj->api_secret();
        $response = $dsFetcherObj->makeApiRequest('get', '/api/products/'. $this->product_sid, $params);
        $product = $response->data;

        $user = auth()->user();
        $quantity = 0;
        $total_order = 0;

        if ($user->isFabricator()) {
            $ledger = Ledger::where('product_sid', $product->sid)->where('party_id', $user->party->id)->first();
            
            if(!empty($ledger)){
                $quantity = $ledger->balance_qty;
            }

        } else {
            $quantity = $this->quantity;
        }

        foreach($this->ledgers() as $ledger){
            $orders = $ledger->orders;
            foreach($orders as $order){
                $total_order += $order->quantity;
            }
        }
       
        return [
            'stock' => $quantity,
            'total_order' => $total_order,
            'active' => $this->active ? true : false,
            'product' => new StockProductResource($product),
            'ledgers' => StockLedgerResource::collection($this->ledgers()),
        ];
    }
}