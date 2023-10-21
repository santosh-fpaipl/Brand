<?php
namespace App\Http\Providers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use App\Models\Supplier;
use App\Http\Resources\SupplierResource;

class SupplierProvider extends Provider
{
    /**
    * Display a listing of the resource.
    */

    public function index(Request $request)
    {
        Cache::forget('suppliers');
        $suppliers = Cache::remember('suppliers', Supplier::getCacheRemember(), function () {
            return Supplier::with('user')->with('addresses')->get();
        });

        // viar = validInternalApiRequest
        $viar = $this->reqHasApiSecret($request);
        foreach ($suppliers as $supplier) {
            if($viar){
                $supplier->viar = true;
            }
        } 

        return ApiResponse::success(SupplierResource::collection($suppliers));
    }

   
}