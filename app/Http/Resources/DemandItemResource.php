<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LedgerItemStockResource;

class DemandItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);

        return [
            "id" => $this->id,
            "stock_id" => new LedgerItemStockResource($this->stock),
            "demand_id" => $this->demand_id,
            "quantity" => $this->quantity,
        ];
    }
}
