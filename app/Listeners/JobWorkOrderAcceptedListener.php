<?php

namespace App\Listeners;

use App\Events\JobWorkOrderAcceptedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Requests\PurchaseCreateRequest;
use App\Http\Providers\PurchaseProvider;
use App\Models\JobWorkOrder;

class JobWorkOrderAcceptedListener
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
    public function handle(JobWorkOrderAcceptedEvent $event): void
    {
        //Log::info($event->jobworkorder);

        $response = Http::post(env('MONAL_APP').'/api/saleorders', [
            'customer_id' => $event->jobworkorder->fabricator_id,
            'customer_sid' => $event->jobworkorder->fabricator_sid,
            'stock_id' => 1,
            'job_work_order_sid' => $event->jobworkorder->sid,
            'quantities' => $event->jobworkorder->quantities,

        ]);

        //Log::info($response);

        if ($response->successful()) {

            $event->jobworkorder->status = JobWorkOrder::FINAL_STATUS;
            $event->jobworkorder->save();
        }

    }
}
