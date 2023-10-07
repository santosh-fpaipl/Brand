<?php
namespace App\Http\Providers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use App\Models\Stock;
use App\Http\Resources\StockResource;
use App\Http\Resources\ShowProductResource;
use App\Http\Requests\StockCreateRequest;
use App\Http\Requests\StockDeleteRequest;
use Exception;

class StockProvider extends Provider
{
    /**
    * Display a listing of the resource.
    */

    public function index()
    {
        $stocks = Stock::select('product_sid', DB::raw('SUM(quantity) as quantity'))->groupBy('product_sid')->where('active', 1)->get();
        return ApiResponse::success(StockResource::collection($stocks));
    }

    /**
    * Create a resource
    */

    public function store(StockCreateRequest $request){

        DB::beginTransaction();
        try{

           // $options = '[{"id": 1},{"id": 2}]';

           // $ranges = '[{"id": 1},{"id": 2},{"id": 3}]';
            
            $options = json_decode($request->options, true);
            $ranges = json_decode($request->ranges, true);
            $product_id = $request->product_id;

            foreach($options as $option){
                $product_option_id = $option['id'];
                foreach($ranges as $range){
                    $product_range_id = $range['id'];
                    $sku = $product_id."-".$product_option_id."-".$product_range_id;
                    $stock = Stock::where('sku', $sku)->first();
                    if(!$stock){
                        Stock::create([
                            'sku' => $sku,
                            'quantity' => 0,
                            'product_id' => $product_id,
                            'product_sid' => $request->product_sid,
                            'product_option_id' => $product_option_id,
                            'product_range_id' => $product_range_id,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success('Data created successfully.');
    }

    /**
     * here we are not showing stock for given id,
     * instead we consider given id to be a product_sid,
     * and return all stock total for that product_sid
     */
    public function show(Request $request, Stock $stock )
    {
        $stock = Stock::groupBy('product_sid')->selectRaw('product_sid ,sum(quantity) as quantity')->where('product_sid', $stock->product_sid)->first();
        return ApiResponse::success(new ShowProductResource($stock));
    }

    public function getStockAddedStatus(Request $request){
        $status = false;
        try{
            $stock = Stock::where('product_sid', $request->product_sid)->first();
            if($stock){
                $status = true;
            }
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(['status' => $status]);
    }
   
    public function deleteStock(StockDeleteRequest $request){

        // We can not delete stock if quantity of any sku of a product is greater than 0
        try{

            $stock = Stock::where('product_sid', $request->product_sid)->where('quantity', '>', 0)->first();

            if($stock){
                throw new Exception('You can not delete product.');
            } else {
                Stock::where('product_sid', $request->product_sid)->forceDelete();
            }
            
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success('Record has been deleted successfully.');
    }
    
}