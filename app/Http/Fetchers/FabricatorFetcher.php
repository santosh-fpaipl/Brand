<?php
namespace App\Http\Fetchers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Fetchers\Fetcher;
use App\Http\Resources\Fabri\FabricatorResource;
use App\Http\Resources\Fabri\PurchaseResource;

use App\Http\Responses\ApiResponse;

class FabricatorFetcher extends Fetcher
{
    public function __construct(){
        parent::__construct(env('FABRI_APP'));
    }

    /**
    * Display a listing of the resource.
    */
    public function allFabricators()
    {
        $params = '?'.$this->api_secret();
        $response = $this->makeApiRequest('get', '/api/fabricators', $params);  
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            return ApiResponse::success(FabricatorResource::collection($response->data));
        }
    }

    /**
     * Display the specified resource.
     */
    public function showFabricator(Request $request, $sid)
    {
        $params = $sid.'?'.$this->api_secret().'&&check='.$request->check;
        $response = $this->makeApiRequest('get', '/api/fabricators/', $params);
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            if(isset($response->data->available) && $response->data->available == true){
                return ApiResponse::success($response->data);
            }      
            return ApiResponse::success(new FabricatorResource($response->data));
        }
    }

    /**
     * Getting Data From Purchase Model
     */
    
    public function allPurchases()
    {
        $params = '?'.$this->api_secret();
        $response = $this->makeApiRequest('get', '/api/purchases', $params);  
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            return ApiResponse::success(PurchaseResource::collection($response->data));
        }
    }

    /**
     * Display the specified resource.
     */
    public function showPurchase(Request $request, $sid)
    {
        $params = $sid.'?'.$this->api_secret().'&&check='.$request->check;
        $response = $this->makeApiRequest('get', '/api/purchases/', $params);
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            if(isset($response->data->available) && $response->data->available == true){
                return ApiResponse::success($response->data);
            }      
            return ApiResponse::success(new PurchaseResource($response->data));
        }
    }

    public function createPurchase(Request $request){
        $params = '?'.$this->api_secret();
        $response = $this->makeApiRequest('post', '/api/purchases', $params, $request->all());
        if($response->status == config('api.error')){
            return ApiResponse::error($response->message, $response->statusCode); 
        } else {
            if(isset($response->data->available) && $response->data->available == true){
                return ApiResponse::success($response->data);
            }      
            return ApiResponse::success(new PurchaseResource($response->data));
        }
    }

    /**
     * End of purchase model
     */
   
}