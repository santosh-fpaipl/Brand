<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Notifications\PurchaseOrderCreateNotification;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class PurchaseOrderCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;

        //$this->user = auth()->user() ? auth()->user() : 'Not Available';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        DB::beginTransaction();
        try {

            // purchaseorder can be placed of stock in draft
            // but not if it's not exits.

            $stock = Stock::where('product_sid', $this->data['product_sid'])->where('active', 1)->exists();
            
            if(!$stock){
                throw new Exception('Stock does not exit.');
            }

            $fabricator = Http::get(env('FABRICATOR_APP').'/api/internal/fabricators/' . $this->data['fabricator_sid']);

            if($fabricator['status'] == config('api.error')){
                throw new Exception('Fabricator does not exit.');
            } 

            $product = Http::get(env('DS_APP').'/api/internal/products/' . $this->data['product_sid']);

            
            if($product['status'] == config('api.error')){
                throw new Exception('Product does not exit.');
            } 

            $messageArr = [['title' => 'Purchase Order placed', 'body' => $this->data['message'], 'username' => $this->data['username'], 'time' => date('Y-m-d H:i:s')]];

            $logArr = [['status' => PurchaseOrder::STATUS[0], 'time' => date('Y-m-d H:i:s')]];


            $po = PurchaseOrder::create([
                'product_id' => $product['data']['id'],
                'product_sid' => $this->data['product_sid'],
                'fabricator_id' => $fabricator['data']['id'],
                'fabricator_sid' => $this->data['fabricator_sid'],
                'sid' => 'PO-' . time() . '-' . $product['data']['id'],
                'quantity' => $this->data['quantity'],
                'quantities' => json_encode($this->data['newDataStructure']),
                'expected_at' => $this->data['expected_at'],
                'message' => json_encode($messageArr),
                'log_status_time' => json_encode($logArr),
            ]);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            //$user->notify(new PurchaseOrderCreateNotification());
           // return ApiResponse::error($e->getMessage(), 404);
        }
    }
}
