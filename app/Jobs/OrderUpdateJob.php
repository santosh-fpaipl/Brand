<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Fetchers\StoreFetcher;
use App\Notifications\OrderUpdateNotification;
use App\Events\ReloadDataEvent;
use App\Models\Order;
use App\Models\User;
use App\Models\Ledger;
use App\Models\Po;
use App\Models\Stock;

class OrderUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order;
    private $data;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, $request)
    {
        $this->order = $order;
        $this->data = [
            'customer_id' => auth()->user()->id, 
            'customer_sid' => auth()->user()->party->sid,
            'stock_id' => 1,
            'purchase_order_sid' => $order->sid,
            'quantities' => json_encode($this->createQuantities($order)),
            'order_id' => $order->id,
            //'expected_at' =>  $request->expected_at,
        ];

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            $storeFetcherrObj = new StoreFetcher();
            $params = '?'.$storeFetcherrObj->api_secret();
            $body = [
                        // 'customer_id' => $this->data['customer_id'], 
                        // 'customer_sid' => $this->data['customer_sid'],
                        'customer_id' => 1, 
                        'customer_sid' => 'C11',
                        'stock_id' => $this->data['stock_id'],
                        'purchase_order_sid' => $this->data['purchase_order_sid'],
                        'quantities' => $this->data['quantities'],
                    ];
            //Log::info($body);
            $response = $storeFetcherrObj->makeApiRequest('post', '/api/saleorders', $params, $body);
            
            if($response->status == config('api.ok')){
                $order = Order::findOrFail($this->data['order_id']);
                $order->status = Order::STATUS[1];
                //$order->expected_at = $this->data['expected_at'];
                $order->update();

                //balance_qty = Total(order-demand)
                //demandable_qty = Total(ready-demand) 
                $ledger = Ledger::findOrFail($order->ledger_id);
                $ledger->balance_qty = $ledger->balance_qty + $order->quantity;
                $ledger->save();

                Po::create([
                    'order_id' => $order->id,
                    'saleorder_id' => $response->data->id,
                ]);
            } 
            DB::commit();
            //To send the message to pusher
            ReloadDataEvent::dispatch(env('PUSHER_MESSAGE'));
            //End of pusher

        } catch(\Exception $e){

            DB::rollBack();
            Log::info($e);
            if(!empty($this->data['customer_id'])){
                $user = User::findOrFail($this->data['customer_id']);
                $user->notify(new OrderUpdateNotification($e->getMessage()));
            }
        }
    }

    private function createQuantities($order){
        $quantities = [];
        foreach($order->orderItems as $orderItem){
            $stock = Stock::findOrFail($orderItem->stock_id);
            $quantities[$stock->product_option_sid.'_'.$stock->product_range_sid] = $orderItem->quantity;
        }
        return $quantities;
    }
}
