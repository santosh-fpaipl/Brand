<?php
namespace App\Http\Responses;
use Illuminate\Http\JsonResponse;

class FetcherResponse{

    public static function success($data, $message = null){
        $response =[
            'data' => $data,
            'status' => config('api.ok'),
            'message' => $message,
        ];

        return new JsonResponse($response, 200);
    }

    public static function error($message, $statusCode){

        $response = [
            'data' => null,
            'status' => config('api.error'),
            'message' => $message,
        ];

        return new JsonResponse($response, $statusCode);
    }
    
}