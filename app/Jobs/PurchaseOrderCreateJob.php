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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Notifications\PurchaseOrderCreateNotification;
use App\Http\Responses\ApiResponse;
use App\Http\Fetchers\DsFetcher;
use App\Http\Fetchers\FabricatorFetcher;
use App\Events\ReloadDataEvent;

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

        $this->user = auth()->user() ? auth()->user() : \App\Models\User::find(1);
        
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
            
            $fabricatorFetcherrObj = new FabricatorFetcher();
            $params = '?'.$fabricatorFetcherrObj->api_secret();
            $response = $fabricatorFetcherrObj->makeApiRequest('get', '/api/fabricators/'.$this->data['fabricator_sid'], $params);
            $fabricator = $response->data;
            if ($response->status == config('api.error')) {
                throw new Exception('#FB145 - Something went wrong, please try again later.');
            }

            
            $dsFetcherObj = new DsFetcher();
            $params = '?'.$dsFetcherObj->api_secret();
            $response = $dsFetcherObj->makeApiRequest('get', '/api/products/'.$this->data['product_sid'], $params);
            $product = $response->data;
            if($response->status == config('api.error')){
                throw new Exception('#FB145 - Something went wrong, please try again later.');
            } 

            //Prepare responses
            $messageArr = [['title' => 'Purchase Order placed', 'body' => $this->data['message'], 'username' => $this->data['username'], 'time' => date('Y-m-d H:i:s')]];
            $logArr = [['status' => PurchaseOrder::STATUS[0], 'time' => date('Y-m-d H:i:s')]];

            PurchaseOrder::create([
                'product_id' => $product->id,
                'product_sid' => $product->sid,
                'fabricator_id' => $fabricator->id,
                'fabricator_sid' => $fabricator->sid,
                'sid' => 'DG-PO-' . time() . '' . $product->id,
                'quantity' => $this->data['quantity'],
                'quantities' => json_encode($this->data['newDataStructure']),
                'expected_at' => $this->data['expected_at'],
                'message' => json_encode($messageArr),
                'log_status_time' => json_encode($logArr),
            ]);

            //To send the message to pusher
                //ReloadDataEvent::dispatch(env('PUSHER_MESSAGE'));
            //End of pusher

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e);
            if(!empty($this->user)){
                $this->user->notify(new PurchaseOrderCreateNotification($e->getMessage()));
            }
        }
    }
}
