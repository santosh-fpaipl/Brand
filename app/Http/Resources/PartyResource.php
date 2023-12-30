<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class PartyResource extends JsonResource
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
            //"id" => $this->id,
            "user" => new UserResource($this->user),
            "sid" => $this->sid,
            "business" => $this->business,
            //"gst" => $this->gst,
            //"pan" => $this->pan,
            "type" => $this->type,
            //"info" => $this->info,
            //"tags" => $this->tags,
            'active' => $this->active,
            "image" => $this->getImage('s100'),
            "stats" => [
                "alloted" => $this->ledgers->count(),
                "running" => $this->ledgers->filter(function ($ledger) {
                    return $ledger->balance_qty > 0;
                })->count(),
                "completed" => $this->ledgers->filter(function ($ledger) {
                    return $ledger->balance_qty == 0 && $ledger->orders->isNotEmpty();
                })->count(),
            ],
        ];
    }
}
