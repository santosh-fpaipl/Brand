<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class JobWorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $products = Cache::remember('products', 24 * 60 * 60, function () {
            $response = Http::get('http://192.168.50.112:8000/api/internal/products'); 
            return $response->json(); // Convert the JSON response to an array
        });

       $jobWorkedOrders = [];
       
       foreach($products['data'] as $product)
       {
            $work = [
                'product_id' => '',
                'fabricator_id' => '1',
                'sid' => '',
                'quantity' => '',
                'quantities' => '',
                'expected_at' => '',
                'message' => '',
            ];

            $quantities = [];

            $product_id = $product['id'];

            $work['product_id'] = $product_id;
            $work['sid'] = 'JWO-'.time().'-'.$product_id;

            foreach($product['options'] as $option){

                $product_option_id = $option['id'];

                foreach($product['ranges'] as $range){

                    $product_range_id = $range['id'];

                    $quantities[$product_option_id."-".$product_range_id] = 10;
                    
                }
            }

            $work['quantity'] = array_sum($quantities);

            $work['quantities'] = $quantities;

            $work['expected_at'] = date('Y-m-d', time());

            array_push( $jobWorkedOrders, $work);

        }
        foreach($jobWorkedOrders as $jobWorkedOrder){
            \App\Models\JobWorkOrder::create([

                'product_id' => $jobWorkedOrder['product_id'],
                'fabricator_id' => $jobWorkedOrder['fabricator_id'],
                'sid' => $jobWorkedOrder['sid'],
                'quantity' => $jobWorkedOrder['quantity'],
                'quantities' => json_encode($jobWorkedOrder['quantities']),
                'expected_at' => $jobWorkedOrder['expected_at'],
                'message' => $jobWorkedOrder['message'],

            ]);
        }

    }
}