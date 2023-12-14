<?php

namespace App\Http\Fetchers;

use App\Http\Fetchers\Fetcher;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\DS\ProductResource;
use App\Http\Resources\DS\ProductSkuResource;
use App\Http\Resources\DS\SkuResource;


class DsFetcher extends Fetcher
{
    public function __construct(){
        parent::__construct(env('DS_APP'));
    }

    /**
    * Get All Products
    */
    public function allProducts()
    {
        $params = '?'.$this->api_secret();
        $response = $this->makeApiRequest('get', '/api/products', $params);  
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            return ApiResponse::success(ProductResource::collection($response->data));
        }
    }

    public function showProduct(Request $request, $sid)
    {
        $params ='?'.$this->api_secret().'&&check='.$request->check;
        $response = $this->makeApiRequest('get', '/api/products/'.$sid, $params);
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            if(isset($response->data->available) && $response->data->available == true){
                return ApiResponse::success($response->data);
            }      
            return ApiResponse::success(new ProductResource($response->data));
        }
    }
    
    public function allProductSkus(){
        $params = '?'.$this->api_secret();
        $response = $this->makeApiRequest('get', '/api/product_skus/', $params);
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            return ApiResponse::success(SkuResource::collection($response->data));
        }
    }

    public function showProductSku($sku){
        $params = '?'.$this->api_secret();
        $response = $this->makeApiRequest('get', '/api/product_skus/'.$sku, $params);
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            return ApiResponse::success(new ProductSkuResource($response->data));
        }
    }
    
}