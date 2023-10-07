<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProductRepository
{
    public static function all()
    {
        $products = Cache::remember('products', 24 * 60 * 60, function () {
            $response = Http::get(env('DS_APP').'/api/internal/products'); 
            return $response->json();
        });

        return $products['data'];
    }

    public static function get($sid)
    {
        $productId = 'product' . $sid;
        Cache::forget($productId);
        $product = Cache::remember($productId, 24 * 60 * 60, function () use($sid) {
            $response = Http::get(env('DS_APP').'/api/internal/products/'.$sid); 
            return $response->json();
        });

        return $product['data'];

        
    }
}