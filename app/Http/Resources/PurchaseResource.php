<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Fetchers\DsFetcher;
use Carbon\Carbon;

class PurchaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        $dsFetcherObj = new DsFetcher();
        $params = $this->product_sid.'?'.$dsFetcherObj->api_secret();
        $response = $dsFetcherObj->makeApiRequest('get', '/api/products/', $params);
        $product = $response->data;

        return [
            "id" => $this->id,
            "sid" => $this->sid,
            "purchase_order" => [
                'sid' => $this->purchaseOrder->sid,
                'quantity' => $this->purchaseOrder->quantity,
                'quantities' => json_decode($this->purchaseOrder->quantities),
                'message' => json_decode($this->purchaseOrder->message),
                'created_at' => $this->purchaseOrder->created_at->format('Y-m-d'),
                'expected_at' => $this->purchaseOrder->expected_at,
            ],
            "product" => [
                'id' => $this->product_id,
                'sid' => $product->sid,
                'name' => $product->name,
                "tags" =>  $product->tags,
                'colors' => ProductOptionResource::collection($product->options),
                'sizes' => ProductRangeResource::collection($product->ranges),
            ],
            "fabricator_id" => $this->fabricator_id,
            "invoice_no" => $this->invoice_no,
            "invoice_date" => $this->invoice_date,
            "quantity" => $this->quantity,
            "quantities" => json_decode($this->quantities),
            "loss_quantity" => $this->loss_quantity,
            "loss_quantities" => json_decode($this->loss_quantities),
            "time_difference" => $this->time_difference, // In days, + means early - means late
            "message" => json_decode($this->message),
            "log_status_time" => json_decode($this->log_status_time),
            "status" => $this->status,
        ];
    }
}
