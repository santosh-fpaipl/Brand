<?php

namespace App\Http\Providers;

use App\Http\Providers\Provider;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\LedgerResource;
use App\Models\Ledger;

class LedgerProvider extends Provider
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Here party_sid is Fabricator Sid  (Optional) , Only need when call this api by staff
        //product_sid is catalog sid
        
        if (env('APP_DEBUG')) {
            Cache::forget('ledgers');
        }
    
        $ledgers = Cache::remember('ledgers', Ledger::getCacheRemember(), function () use ($request) {
            $user = auth()->user();
            if ($user->isStaff()) {
                return Ledger::staffLedgers($request->product_sid, $request->party_sid)->get();
            } elseif ($user->isFabricator()) {
                return Ledger::fabricatorLedgers($request->product_sid, $user->party->id)->get();
            } else {
                return Ledger::managerLedgers()->get();
            }
        });
    
        return ApiResponse::success(LedgerResource::collection($ledgers));
    }

    /**
     * Display the specified resource.
     * 
     * same as index
     */
    public function show(Request $request, Ledger $ledger)
    {
       if(auth()->user()->isFabricator()){
            if($ledger->ledger->party_id != auth()->user()->party->id){
                return ApiResponse::error('Invalid request', 422);
            }
        }
        if (env('APP_DEBUG')) {
            Cache::forget('ledger' . $ledger);
        }
       
        $ledger = Cache::remember('ledger' . $ledger, Ledger::getCacheRemember(), function () use ($ledger) {
            return $ledger;
        });

        // viar = validInternalApiRequest
        $viar = $this->reqHasApiSecret($request);
        if($viar){
            $ledger->viar = true;
        }
        return ApiResponse::success(new LedgerResource($ledger));
    }

     
}
