<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\AdjustmentItemResource;

class AdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => new UserResource($this->user),
            'ledger_id' => $this->ledger_id,
            'quantity' => $this->quantity,
            'type' => $this->type,
            'note' =>  ChatResource::collection($this->chats),
            'created_at' => $this->created_at,
            'adjustmentItems' => AdjustmentItemResource::collection($this->adjustmentItems),
        ];
    }
}
