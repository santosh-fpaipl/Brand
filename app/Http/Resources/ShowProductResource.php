<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Fetchers\DsFetcher;
use App\Http\Resources\StockProductResource;


class ShowProductResource extends JsonResource
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
        $response = $dsFetcherObj->makeApiRequest('get', '/api/products/'. $this->product_sid, $params);
        $product = $response->data;
       
        return [
            'stock' => $this->quantity,
            'active' => $this->active?true:false,
            'product' => new StockProductResource($product),
        ];
    }
}