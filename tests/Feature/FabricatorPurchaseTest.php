<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FabricatorPurchaseTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get('/api/fabri/purchases');
        $response->assertStatus(200);
    }

    public function testStore()
    {
        $purchaseOrder = $this->createPurchaseOrder();

        // $response = $this->createPurchase($purchaseOrder->sid);

        // $response->assertStatus(200);
    }


    public function createPurchase($sid){
        // To create the purchase
            $purchase = $this->post('/api/fabri/purchases',[
                'po_sid' => $sid,
                'so_sids' => '{"0":MC-SO1023-0001}',
            ]);
            
        // End of purchase order

        return $purchase;
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
