<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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
use App\Http\Providers\LedgerAdjustmentProvider;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->prefix('')->group(function () {    
    Route::resource('stocks', StockProvider::class);
    //check this product is added in stock or not
    Route::get('stocks/status/{product_sid}',[StockProvider::class, 'getStockAddedStatus']);
    Route::post('stocks/delete',[StockProvider::class, 'deleteStock']);
    //Route::get('stockbyproduct/{product_id}', [StockProvider::class, 'stockByProduct']);
});


Route::middleware('auth:sanctum')->prefix(env('DS_APP_PREFIX'))->group(function () {   
    Route::get('products', [DsFetcher::class, 'allProducts']);
    Route::get('products/{sid}', [DsFetcher::class, 'showProduct']);
    Route::get('product_skus', [DsFetcher::class, 'allProductSkus']);
    Route::get('product_skus/{sku}', [DsFetcher::class, 'showProductSku']);
});




Route::middleware('auth:sanctum')->group(function(){
    Route::resource('users', UserProvider::class);
    Route::resource('parties', PartyProvider::class);
    Route::resource('orders', OrderProvider::class);
    Route::resource('readies', ReadyProvider::class);
    Route::resource('demands', DemandProvider::class);
    Route::resource('chats', ChatProvider::class);
    Route::resource('ledgers', LedgerProvider::class);
    Route::post('procurement/check', [PoProvider::class, 'checkProcurement']);
    Route::resource('ledgeradjustments', LedgerAdjustmentProvider::class);
});
