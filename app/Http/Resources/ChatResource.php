<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\UserResource;

class ChatResource extends JsonResource
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
            "message" => $this->message,
            'ledger_id' => $this->ledger_id,
            "sender_id" => new UserResource($this->user),
            "delivered_at" => $this->delivered_at,
            "recevied_at" => $this->recevied_at,
            "read_at" => $this->read_at,

        ];
    }
}
