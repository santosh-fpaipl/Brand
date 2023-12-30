<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StockLedgerItemResource;
use App\Http\Resources\ChatResource;
use App\Http\Resources\PartyResource;

class LedgerResource extends JsonResource
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
            "id" =>  $this->id,
            "sid" =>  $this->sid,
            "name" =>  $this->name,
            "product_sid" =>  $this->product_sid,
            "product_id" =>  $this->product_id,
            "party_id" =>  new PartyResource($this->party),
            "balance_qty" =>  $this->balance_qty,
            "demandable_qty" =>  $this->demandable_qty,
            'LedgerItems' => new LedgerItemsLedgerResource([
                $this->orders, 
                $this->readies, 
                $this->demands, 
                $this->adjustments
            ]),
            'chats' => ChatResource::collection($this->chats),
        ];
    }
}
