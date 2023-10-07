<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AddressResource;

class SupplierResource extends JsonResource
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
            "name" => $this->user->name,
            "email" => $this->user->email,
            "business_name" => $this->business_name,
            "gst" => $this->gst,
            "pan" => $this->pan,
            "sid" => $this->sid,
            "description" => $this->description,
            "addresses" => AddressResource::collection($this->addresses),
        ];
    }
}
