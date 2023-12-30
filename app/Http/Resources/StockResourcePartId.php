<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Fetchers\DsFetcher;
use App\Http\Resources\productResource;
use App\Http\Resources\ProductOptionResource;
use App\Http\Resources\ProductRangeResource;
use App\Models\Ledger;
use App\Http\Resources\StockLedgerItemResource;
use Carbon\Carbon;
class StockResourcePartId extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);

        $dsFetcherObj = new DsFetcher();
        $params = '?'.$dsFetcherObj->api_secret();
        $response = $dsFetcherObj->makeApiRequest('get', '/api/products/'.$this->product_sid, $params);
        $product = $response->data;

        return [
            'stock' => $this->stock->quantity,
            'newOrder' => 0,
            'active' => $this->active ? true : false,
            'product' => new productResource($product),
        ];

        // $user = auth()->user();
        // $quantity = 0;
        // $newOrder = false;

        // if ($user->isFabricator()) {

        //     // $ledger = Ledger::where('product_sid', $product->sid)->where('party_id', $user->party->id)->first();
        //     $ledgersForStock = $this->ledgers($user->party->id);
            
        //     if(!empty($ledgersForStock)){

        //         $ledger = $ledgersForStock;

        //         $quantity = $ledger->balance_qty;

        //         $ledgerItemCollections = new StockLedgerItemResource([$ledger->orders, $ledger->readies, $ledger->demands, $ledger->ledgerAdjustments]);
                
        //         $ledgerItem_arr = json_decode(($ledgerItemCollections->toJson()),true);

        //         $lst_ledgerItem = end($ledgerItem_arr);
               
        //         if(strtolower($lst_ledgerItem['model']) == 'order'){
        //             $newOrder = true;
        //         }
        //     }

        // } else {

        //     $ledgersForStock = $this->ledgers();
        //     $quantity = $this->quantity;
        // }

        // $ledgers_latest_orders = [];

        // foreach($ledgersForStock as $ledger){
        //     $order = $ledger->getLatestOrder();
        //     if ($order) {
        //         array_push($ledgers_latest_orders, [
        //             'id' => $order->id,
        //             'created_at' => $order->created_at
        //         ]);
        //     }
        // }
        
        // return [
        //     $ledgers_latest_orders
        // ];
        

        // usort($ledgers_latest_orders, function($a, $b) {
        //     return strtotime($b['order_created']) - strtotime($a['order_created']);
        // });
        
        // return [
        //     'created' => Carbon::parse($latest_orders[0]['order_created'])->timestamp,
        //     'stock' => $quantity,
        //     'newOrder' => $newOrder,
        //     'active' => $this->active?true:false,
        //     // 'product' => new productResource($product),
        // ];
    }
}