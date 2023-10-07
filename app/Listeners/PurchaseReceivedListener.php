<?php

namespace App\Listeners;

use App\Events\PurchaseReceivedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Stock;

class PurchaseReceivedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PurchaseReceivedEvent $event): void
    {
        Log::info($event->purchase);

        Cache::forget('product');
        $product = Cache::remember('product', 24 * 60 * 60, function () use($event) {
            $respons = Http::get(env('DS_APP').'/api/internal/products/'.$event->purchase->product_sid); 
            return $respons->json(); // Convert the JSON response to an array
        });
        
        $product_id = $product['data']['id'];

        foreach(json_decode($event->purchase->quantities, true) as $option_size => $quantity){

            $option_size_arr = explode("_", $option_size);
            $option_id = $this->searchForSid($option_size_arr[0], $product['data']['options']);
            $size_id = $this->searchForSid($option_size_arr[1], $product['data']['ranges']);
           
            if(!is_null($option_id) && !is_null($option_id)){

                $sku = $product_id."-".$option_id."-".$size_id;
                $stock = Stock::where('sku', $sku)->first();
                
                if($stock){
                    $stock->quantity += $quantity;
                    $stock->save();
                } else {
                    Stock::create([
                        'sku' => $sku,
                        'quantity' => $quantity,
                        'product_id' => $product_id,
                        'product_sid' => $product['sid'],
                        'product_option_id' => $option_id,
                        'product_range_id' => $size_id,
                    ]);
                }
            }
           
        }
            
       
    }

    function searchForSid($sid, $array) {
        foreach ($array as $key => $val) {
            if ($val['sid'] === $sid) {
                return $val['id'];
            }
        }
        return null;
     }

}
