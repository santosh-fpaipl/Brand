<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $response = $this->get('/api/purchases', [
            'status' => 'completed' 
        ]);

        $response->assertStatus(200);
    }

    public function testStore()
    {
        // To create the purchase order
            $purchaseOrder = $this->createPurchaseOrder();
        // End of purchase order

        $response = $this->createPurchase($purchaseOrder);

        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        // To create the purchase order
            $purchaseOrder = $this->createPurchaseOrder();
        // End of purchase order

        //Create of Purchase
            $this->createPurchase($purchaseOrder);
            $purchase = \App\Models\Purchase::first();
        //End of Purchase

        $response = $this->put('/api/purchases/'.$purchase->sid,[
            'message' => '{"title":"Message Update","body":"Test Case"}',
            'status' => 'next',
            'quantities' => '[{"green1_S1":200},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]',
        ]);

        $response->assertStatus(200);
    }

    public function testShow()
    {
        // To create the purchase order
            $purchaseOrder = $this->createPurchaseOrder();
        // End of purchase order

        //Create of Purchase
            $this->createPurchase($purchaseOrder);
            $purchase = \App\Models\Purchase::first();
        //End of Purchase
        $response = $this->get('/api/purchases/'.$purchase->sid);
        $response->assertStatus(200);
    }

    public function testGetPurchaseMessage()
    {
        // To create the purchase order
            $purchaseOrder = $this->createPurchaseOrder();
        // End of purchase order

        //Create of Purchase
            $this->createPurchase($purchaseOrder);
            $purchase = \App\Models\Purchase::first();
        //End of Purchase

        $response = $this->get('/api/purchases/message/'.$purchase->sid);
        $response->assertStatus(200);
    }


    public function createPurchaseOrder(){
        // To create the purchase order
            $stockResponse = $this->post('/api/stocks',[
                'product_sid' => 'product1',
            ]);

            if($stockResponse->assertStatus(200)){
                $stock = \App\Models\Stock::first();
            }

            $this->post('/api/purchaseorders',[
                'fabricator_sid' => 'F1',
                'product_sid' => $stock->product_sid,
                'quantities' => '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]',
                'expected_at' => date('Y-m-d', time()),
                'message' => 'Hello Test Case'

            ]);
            //Run Queue in memeory
            Queue::fake();
            $purchaseOrder = \App\Models\PurchaseOrder::first();
        // End of purchase order

        return $purchaseOrder;
    }

    public function createPurchase($purchaseOrder){
        $purchase = $this->post('/api/purchases',[
            'purchase_order_sid' => $purchaseOrder->sid,
            'quantities' => '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]',
            'invoice_no' => '111111',
            'invoice_date' => date('Y-m-d', time()),
            'message' => 'Hello Test Case'

        ]);

        return $purchase;
    }

}
