<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ChatResource;
use App\Http\Resources\PartyResource;
use App\Http\Resources\StockLedgerItemResource;

class StockLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [          
            "sid" =>  $this->sid,
            "party" =>  new PartyResource($this->party),
            'chats' => ChatResource::collection($this->chats),
            "balance_qty" =>  $this->balance_qty,
            "demandable_qty" =>  $this->demandable_qty,
            'records' => new StockLedgerItemResource([
                $this->orders, 
                $this->demands, 
                $this->readies, 
                $this->adjustments
            ]),
        ];
    }
}
