<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cache::forget('products');
        $products = Cache::remember('products', 24 * 60 * 60, function () {
            $response = Http::get('http://192.168.1.133:8000/api/internal/products'); 
            return $response->json(); // Convert the JSON response to an array
        });
        
        foreach($products['data'] as $product){

            $product_id = $product['id'];
            foreach($product['options'] as $option){
                $product_option_id = $option['id'];
                foreach($product['ranges'] as $range){
                    $product_range_id = $range['id'];
                    \App\Models\Stock::create([
                        'sku' => $product_id."-".$product_option_id."-".$product_range_id,
                        'quantity' => 100,
                        'product_id' => $product_id,
                        'product_sid' => $product['sid'],
                        'product_option_id' => $product_option_id,
                        'product_range_id' => $product_range_id,
                    ]);
                }
            }
            //$this->command->info($product['name']);
        }
    }
}