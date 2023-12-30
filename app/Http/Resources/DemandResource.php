<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\DemandItemResource;

class DemandResource extends JsonResource
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
            'id' => $this->id,
            'sid' => $this->sid,
            'ledger_id' => $this->ledger_id,
            'quantity' => $this->quantity,
            'expected_at' => \Carbon\Carbon::parse($this->expected_at)->format('d-m-Y'),
            'status' => $this->status,
            'user' => new UserResource($this->user),
            'note' =>  ChatResource::collection($this->chats),
            'demandItems' => DemandItemResource::collection($this->demandItems),
        ];
    }
}
