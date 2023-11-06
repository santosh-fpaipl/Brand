<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FabricatorTest extends TestCase
{
    use RefreshDatabase;

    public $fabricator;

    public function setUp(): void 
    {
        parent::setUp();
        $this->fabricator = 'F1';
    }

    public function testIndex()
    {
        $response = $this->get('/api/fabri/fabricators');
        $response->assertStatus(200);
    }

    public function testShow()
    {
        $response = $this->get('/api/fabri/fabricators/'.$this->fabricator);
        $response->assertStatus(200);
    }
}
