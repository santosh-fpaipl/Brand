<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DsProductTest extends TestCase
{
    use RefreshDatabase;

    public $product_sid;
    public $product_sku;

    public function setUp(): void 
    {
        parent::setUp();
        $this->product_sid = 'product1';
        $this->product_sku = '1-1-2';
    }

    public function testIndex()
    {
        $response = $this->get('/api/ds/products');
        $response->assertStatus(200);
    }

    public function testShow()
    {
        $response = $this->get('/api/ds/products/'.$this->product_sid);
        $response->assertStatus(200);
    }

    public function testAllProductBySku()
    {
        $response = $this->get('/api/ds/product_skus');
        $response->assertStatus(200);
    }

    public function testShowProductBySku()
    {
        $response = $this->get('/api/ds/product_skus/'.$this->product_sku);
        $response->assertStatus(200);
    }

    
}
