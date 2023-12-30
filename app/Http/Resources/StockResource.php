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

class StockResource extends JsonResource
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

        $user = auth()->user();
        $quantity = 0;
        $newOrder = false;
        if ($user->isFabricator()) {

            $ledger = Ledger::where('product_sid', $product->sid)->where('party_id', $user->party->id)->first();
            
            if(!empty($ledger)){

                $quantity = $ledger->balance_qty;

                $ledgerItemCollections = new StockLedgerItemResource([$ledger->orders, $ledger->readies, $ledger->demands, $ledger->ledgerAdjustments]);
                
                $ledgerItem_arr = json_decode(($ledgerItemCollections->toJson()),true);

                $lst_ledgerItem = end($ledgerItem_arr);
               
                if(strtolower($lst_ledgerItem['model']) == 'order'){
                    $newOrder = true;
                }
            }
        } else {
            $quantity = $this->quantity;
        }

       
        return [
            'stock' => $quantity,
            'newOrder' => $newOrder,
            'active' => $this->active?true:false,
            'product' => new productResource($product),
        ];
    }
}