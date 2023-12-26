<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Fetchers\DsFetcher;

class LedgerItemStockResource extends JsonResource
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
            "id" => $this->id,
            "sku" => $this->sku,
            "quantity" => $this->quantity,
            "product_sid" => $this->product_sid,
            "product_id" => $this->product_id,
            "product_option_id" => $this->product_option_id,
            "product_option_sid" => $this->product_option_sid,
            "product_range_id" => $this->product_range_id,
            "product_range_sid" => $this->product_range_sid,
            "active" => $this->active,
            "note" => $this->note,
            'image' => $this->getFirstImage($product->options, $this->product_option_id), // Implement this function in your model or here
        ];
    }

    /**
     * Optionally, implement this function to fetch the first image
     * You could fetch it from a relation or some other way.
     */
    private function getFirstImage($options, $optionId): string
    {
        foreach($options as $option){
            if($option->id == $optionId){
                return $option->image;
            }

        }
        // Your implementation here
        //return collect($options)->first()->image;
    }
}
