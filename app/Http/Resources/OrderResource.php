<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\ChatResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        

        return [
            'id' => $this->id,
            'sid' => $this->sid,
            'ledger_id' => $this->ledger_id,
            'quantity' => $this->quantity,
            'expected_at' => \Carbon\Carbon::parse($this->expected_at)->format('d-m-Y'),
            'log_status_time' => json_decode($this->log_status_time),
            'status' => $this->status,
            'user' => new UserResource($this->user),
            'reject' => $this->reject ? true : false,
            'note' => ChatResource::collection($this->chats),
            'orderItems' => OrderItemResource::collection($this->orderItems),
        ];
    }
}