<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductOptionResource;
use App\Http\Resources\ProductRangeResource;

use App\Http\Fetchers\DsFetcher;

class PurchaseOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dsFetcherObj = new DsFetcher();
        $params = '?'.$dsFetcherObj->api_secret();
        $response = $dsFetcherObj->makeApiRequest('get', '/api/products/'.$this->product_sid, $params);
        $product = $response->data;
        
        return [
            'id' => $this->viar ? $this->id : '',
            'sid' => $this->sid,
            'product_id' => $this->product_id,
            'name' => $product->name,
            "tags" =>  $product->tags,
            'quantity' => $this->quantity,
            'message' => json_decode($this->message),
            'created_at' => $this->created_at->format('Y-m-d'),
            'expected_at' => $this->expected_at,
            'fabricator_id' => $this->fabricator_id,
            'fabricator_sid' => $this->fabricator_sid,
            'colors' => ProductOptionResource::collection($product->options),
            'sizes' => ProductRangeResource::collection($product->ranges),
            'quantities' => json_decode($this->quantities),
            'status' => $this->status,
            'log_status_time' => json_decode($this->log_status_time),
            'procured' => $this->fabricatorHasPurchaseFabric(),
            'purchased' => $this->purchases->count() ? 1 : 0,
            //'procured' => $this->monalHasSoldFabric(),
        ];
    }
}