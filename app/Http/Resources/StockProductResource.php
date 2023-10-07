<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductOptionResource;
use App\Http\Resources\ProductRangeResource;

class StockProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       // return parent::toArray($request);

       return [
        'name' => $this['name'],
        'id' => $this['id'],
        'sid' => $this['sid'],
        'image' => $this->getFirstImage($this['options']),
        'options' => ProductOptionResource::collection($this['options']),
        'ranges' => ProductRangeResource::collection($this['ranges']),

    ];
    }

    private function getFirstImage($options): string
    {
        // Your implementation here
        return collect($options)->first()['image'];
    }
}
