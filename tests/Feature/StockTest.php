<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    private $product_sid = 'product1';
    
    public function testIndex()
    {
        $response = $this->get('/api/stocks', [
            'type' => ''  //sku or ''
        ]);

        $response->assertStatus(200);
    }

    public function testStore(){

        $response = $this->createStock();

        $response->assertOk();
    }


    public function testUpdate(){

        $this->createStock();

        $response = $this->put('/api/stocks/'.$this->product_sid, [
            'active' => 'true', // true or false
        ]);

        $response->assertOk();
    }

    public function testShow(){

        $this->createStock();

        $response = $this->get('/api/stocks/'.$this->product_sid);

        $response->assertOk();
    }

    public function testStockStatus(){

        $this->createStock();

        $response = $this->get('/api/stocks/status/'.$this->product_sid);

        $response->assertOk();
    }

    public function testDeleteStock(){

        $this->createStock();

        $response = $this->post('/api/stocks/delete', [
            'product_sid' => $this->product_sid,
        ]);

        $response->assertOk();
    }


    public function createStock(){

        $stock = $this->post('/api/stocks', [
            'product_sid' => $this->product_sid,
        ]);

        return $stock;
    }
}
