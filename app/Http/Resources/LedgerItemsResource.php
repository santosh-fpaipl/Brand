<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LedgerItemStockResource;

class LedgerItemsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "stock_id" => new LedgerItemStockResource($this->stock),
            "quantity" => $this->quantity,
        ];
    }
}
