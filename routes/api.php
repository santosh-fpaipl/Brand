<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Providers\PurchaseOrderProvider;
use App\Http\Providers\StockProvider;
use App\Http\Providers\SupplierProvider;
use App\Http\Providers\PurchaseProvider;
use App\Http\Providers\SaleProvider;
use App\Http\Fetchers\DsFetcher;
use App\Http\Fetchers\FabricatorFetcher;

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

Route::prefix('charts')->group(function () {  

    Route::get('/test', function(){

        $array = [
            'labels' => ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange', 'Black', 'Pink'],
        ];

        return $array;
    });

    Route::get('/test2', function(){

        $array = [
            'labels' => ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange', 'Black', 'Pink'],
            'datasets' => [
                [
                    'label' => '# of Votes',
                    'data' => [15, 19, 3, 5, 2, 3, 10, 9.5],
                    'borderWidth' => 2,
                    'backgroundColor' => ['red']
                ],
                
            ]
        ];

        return $array;
    });

    Route::get('/sale', [SaleProvider::class, 'sales']);

});


Route::prefix('')->group(function () {    
    Route::resource('purchaseorders', PurchaseOrderProvider::class);
    Route::get('purchaseorders/message/{po_sid}',[PurchaseOrderProvider::class, 'getPurchaseOrderMessage']);
    Route::resource('stocks', StockProvider::class);
    //check this product is added in stock or not
    Route::get('stocks/status/{product_sid}',[StockProvider::class, 'getStockAddedStatus']);
    Route::post('stocks/delete',[StockProvider::class, 'deleteStock']);
    Route::resource('suppliers', SupplierProvider::class);
    //Route::get('stockbyproduct/{product_id}', [StockProvider::class, 'stockByProduct']);
    Route::resource('purchases', PurchaseProvider::class);
    Route::get('purchases/message/{sid}',[PurchaseProvider::class, 'getPurchaseMessage']);
});



Route::prefix(env('DS_APP_PREFIX'))->group(function () {   
    Route::get('products', [DsFetcher::class, 'allProducts']);
    Route::get('products/{sid}', [DsFetcher::class, 'showProduct']);
    Route::get('product_skus', [DsFetcher::class, 'allProductSkus']);
    Route::get('product_skus/{sku}', [DsFetcher::class, 'showProductSku']);
});

Route::prefix(env('FABRI_APP_PREFIX'))->group(function () {   
    Route::get('fabricators', [FabricatorFetcher::class, 'allFabricators']);
    Route::get('fabricators/{sid}', [FabricatorFetcher::class, 'showFabricator']);
    Route::get('purchases', [FabricatorFetcher::class, 'allPurchases']);
    Route::get('purchases/{sid}', [FabricatorFetcher::class, 'showPurchase']);
    Route::post('purchases', [FabricatorFetcher::class, 'createPurchase']);
});
    
