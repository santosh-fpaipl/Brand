<?php

namespace App\Listeners;

use App\Events\PurchaseOrderAcceptedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Fetchers\StoreFetcher;
use App\Models\PurchaseOrder;

class PurchaseOrderAcceptedListener
{
    public $fabrics;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->fabrics = [
            [
                'fabric_sid' => 'M11-M001-S001',
                'fcpu' => 1.5,
                'color_sid' => 'base'  // base or any color sid, base means catalog color same as fabric color
            ]
        ];

        // $this->fabrics = [
        //     'green1' => [
        //         [
        //             'fabric_sid' => 'M11-M001-S001',
        //             'fcpu' => [
        //                 [
        //                     'size_sid' => 'S1',
        //                     'quantity' => 1.5,
        //                 ],
        //                 [
        //                     'size_sid' => 'M1',
        //                     'quantity' => 1.5,
        //                 ],
        //                 [
        //                     'size_sid' => 'L1',
        //                     'quantity' => 1.5,
        //                 ]
        //             ],
        //             'color_sid' => 'base'  // base or any color sid, base means catalog color same as fabric color
        //         ],
        //         [
        //             'fabric_sid' => 'M11-M001-S001',
        //             'fcpu' => 0.5,
        //             'color_sid' => 'white'  // base or any color sid, base means catalog color same as fabric color
        //         ]
        //         ],
        //     'blue1' => [
        //         [
        //             'fabric_sid' => 'M11-M001-S001',
        //             'fcpu' => 1.5,
        //             'color_sid' => 'base'  // base or any color sid, base means catalog color same as fabric color
        //         ],
        //         [
        //             'fabric_sid' => 'M11-M001-S001',
        //             'fcpu' => 0.5,
        //             'color_sid' => 'white'  // base or any color sid, base means catalog color same as fabric color
        //         ]
        //     ]
        // ];
    }

    /**
     * Handle the event.
     */
    public function handle(PurchaseOrderAcceptedEvent $event): void
    {
        Log::info($event->purchaseorder);

        $storeFetcherObj = new StoreFetcher();
        $params = '?'.$storeFetcherObj->api_secret();
        $body = [
            'customer_id' => $event->purchaseorder->fabricator_id,
            'customer_sid' => $event->purchaseorder->fabricator_sid,
            'stock_id' => 1,
            'purchase_order_sid' => $event->purchaseorder->sid,
            'quantities' => $event->purchaseorder->quantities,
        ];
        $response = $storeFetcherObj->makeApiRequest('post', '/api/saleorders', $params, $body);

        if ($response->statusCode == 200 && $response->status == config('api.ok')) {

            $event->purchaseorder->status = PurchaseOrder::FINAL_STATUS;
            $event->purchaseorder->save();
        }

    }
}
