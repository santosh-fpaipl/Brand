<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Providers\StockProvider;
use App\Http\Fetchers\DsFetcher;
use App\Http\Providers\UserProvider;
use App\Http\Providers\PartyProvider;
use App\Http\Providers\OrderProvider;
use App\Http\Providers\ReadyProvider;
use App\Http\Providers\DemandProvider;
use App\Http\Providers\ChatProvider;
use App\Http\Providers\LedgerProvider;
use App\Http\Providers\PoProvider;
use App\Http\Providers\AdjustmentProvider;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {   
    // Checked 

    Route::get('fabstock', [StockProvider::class, 'newOrderStock']);


    Route::post('stocks/query',[StockProvider::class, 'query']);
    Route::apiResource('stocks', StockProvider::class);
    // Pending
    Route::resource('users', UserProvider::class);
    Route::resource('parties', PartyProvider::class);

    Route::resource('orders', OrderProvider::class);
    Route::resource('readies', ReadyProvider::class);
    Route::resource('demands', DemandProvider::class);
    
    Route::resource('chats', ChatProvider::class);
    Route::resource('ledgers', LedgerProvider::class);
    Route::resource('ledgeradjustments', AdjustmentProvider::class);
    Route::post('procurement/check', [PoProvider::class, 'checkProcurement']);
    Route::get('orders/reject/{order}', [OrderProvider::class, 'reject']);

});


Route::middleware('auth:sanctum')->prefix(env('DS_APP_PREFIX'))->group(function () {   
    Route::get('products', [DsFetcher::class, 'allProducts']);
    Route::get('products/{sid}', [DsFetcher::class, 'showProduct']);
    Route::get('product_skus', [DsFetcher::class, 'allProductSkus']);
    Route::get('product_skus/{sku}', [DsFetcher::class, 'showProductSku']);
});
