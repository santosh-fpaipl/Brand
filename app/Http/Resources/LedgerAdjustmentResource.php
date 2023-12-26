<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\UserResource;

class LedgerAdjustmentResource extends JsonResource
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
            'user_id' => new UserResource($this->user),
            'ledger_id' => $this->ledger_id,
            'order_qty' => $this->order_qty,
            'ready_qty' => $this->ready_qty,
            'demand_qty' => $this->demand_qty,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
