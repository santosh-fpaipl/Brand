<?php

namespace App\Http\Providers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseProvider;

class Provider extends BaseProvider
{
    use AuthorizesRequests, ValidatesRequests;

    public function reqHasApiSecret($request){
        $viar = false;
        if($request->has('api_secret') && env('API_SECRET') == $request->get('api_secret') ){
            $viar = true;
        }
        return $viar;
    }


}