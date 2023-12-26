<?php

namespace App\Http\Fetchers;

use App\Http\Fetchers\Fetcher;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\STORE\SaleOrderResource;



class StoreFetcher extends Fetcher
{
    public function __construct(){
        parent::__construct(env('STORE_APP'));
    }
    
}