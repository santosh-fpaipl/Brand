<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\UserResource;
use App\Http\Resources\LedgerOrderItemsResource;
use App\Http\Resources\LedgerReadyItemsResource;
use App\Http\Resources\LedgerDemandItemsResource;

class LedgerItemsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        $flattenedItems = $this->collection->collapse()->map(function ($item) {
            return [
                'id' => $item->id,
                'sid' => $item->sid,
                'model' => Str::afterLast(get_class($item), '\\'),
                'quantity' => $item->quantity,
                'expected_at' => $item->expected_at ?? null, // Some items might not have expected_at
                'log_status_time' => json_decode($item->log_status_time ?? null), // Only for orders
                'status' => $item->status ?? null, // Some items might not have status
                'user' => new UserResource($item->user),
                //only for adjustment
                'order_qty' => $item->order_qty,
                'ready_qty' => $item->ready_qty,
                'demand_qty' => $item->demand_qty,
                'note' => $item->note,
                //end of adjustment
                'created_at' => $item->created_at,
                'items' => $this->resolveItemResource($item),
            ];
        })->toArray();

        // Sort the array by 'created_at'
        usort($flattenedItems, function ($a, $b) {
            return strtotime($a['created_at']) <=> strtotime($b['created_at']);
        });

        return [
            'LedgerItems' => $flattenedItems
        ];
    }

    /**
     * Resolve the appropriate item resource.
     *
     * @param mixed $item
     * @return mixed
     */
    private function resolveItemResource($item)
    {
        if ($item->orderItems ?? null) {
            return LedgerOrderItemsResource::collection($item->orderItems);
        } elseif ($item->readyItems ?? null) {
            return LedgerReadyItemsResource::collection($item->readyItems);
        } elseif ($item->demandItems ?? null) {
            return LedgerDemandItemsResource::collection($item->demandItems);
        }

        return null;
    }
}
