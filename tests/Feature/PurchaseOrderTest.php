<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;


class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;
      

    public function testIndex()
    {
        $response = $this->get('/api/purchaseorders');
        $response->assertStatus(200);
    }

    public function testStore()
    {
        $response = $this->createPurchaseOrder();

        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        // Create purchaseorder
            $this->createPurchaseOrder();
        // End of purchaseorder

        //Run Queue in memeory to create purchaseorder
            Queue::fake();
            $purchaseOrder = \App\Models\PurchaseOrder::first();
        
        $response = $this->put('/api/purchaseorders/'.$purchaseOrder->sid,[
            'message' => '{"title":"Message Update","body":"Test Case"}',
            'status' => 'next',
            'quantities' => '[{"green1_S1":200},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]',
            'expected_at' => date('Y-m-d', time()),
        ]);

        $response->assertStatus(200);
    }

    public function testShow()
    {
        // Create purchaseorder
            $this->createPurchaseOrder();
        // End of purchaseorder

        //Run Queue in memeory
            Queue::fake();
            $purchaseOrder = \App\Models\PurchaseOrder::first();

        $response = $this->get('/api/purchaseorders/'.$purchaseOrder->sid);
        $response->assertStatus(200);
    }

    public function testGetPurchaseOrderMessage()
    {
        // Create purchaseorder
            $this->createPurchaseOrder();
        // End of purchaseorder

        //Run Queue in memeory
            Queue::fake();
            $purchaseOrder = \App\Models\PurchaseOrder::first();

        $response = $this->get('/api/purchaseorders/message/'.$purchaseOrder->sid);
        $response->assertStatus(200);

        // if($response->assertStatus(200)){
        //     $data = json_decode($response->getContent(), true);  
        //     //dd($data['data']['message']);
        // }
    }

    public function createPurchaseOrder(){
        // To create the purchase order
            $stockResponse = $this->post('/api/stocks',[
                'product_sid' => 'product1',
            ]);

            if($stockResponse->assertStatus(200)){
                $stock = \App\Models\Stock::first();
            }

            $purchaseOrder = $this->post('/api/purchaseorders',[
                'fabricator_sid' => 'F1',
                'product_sid' => $stock->product_sid,
                'quantities' => '[{"green1_S1":1313},{"green1_M1":1313},{"green1_L1":1313},{"blue1_S1":1313},{"blue1_M1":1313},{"blue1_L1":1313}]',
                'expected_at' => date('Y-m-d', time()),
                'message' => 'Hello Test Case'

            ]);
            
        // End of purchase order

        return $purchaseOrder;
    }

}
