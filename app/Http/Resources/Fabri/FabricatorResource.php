<?php

namespace App\Http\Resources\Fabri;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Fabri\AddressResource;

class FabricatorResource extends JsonResource
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
            "sid" => $this->sid,
            "name" => $this->name,
            "email" => $this->email,
            "description" => $this->description,
            "addresses" => AddressResource::collection($this->addresses),
        ];
    }
}
