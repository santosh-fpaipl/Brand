<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\ProductRepository;
use App\Http\Resources\productResource;
use App\Http\Resources\ProductOptionResource;
use App\Http\Resources\ProductRangeResource;

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

        $product = ProductRepository::get($this->product_sid);
       
        return [
            'stock' => $this->quantity,
            'product' => new productResource($product),
        ];
    }
}