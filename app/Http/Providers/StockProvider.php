<?php
namespace App\Http\Providers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Providers\Provider;
use App\Http\Fetchers\DsFetcher;
use App\Http\Responses\ApiResponse;
use App\Models\Stock;
use App\Http\Resources\StockResource;
use App\Http\Resources\ShowProductResource;
use App\Http\Resources\StockSkuResource;
use App\Http\Requests\StockRequest;
use App\Http\Requests\StockUpdateRequest;
use Exception;

class StockProvider extends Provider
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        if($request->has('type') && $request->type == 'sku'){
            //To get stock sku wise
            $stocks = Stock::all();
        } else {
            $stocks = Stock::select('product_sid', DB::raw('SUM(quantity) as quantity'),'active')->groupBy('product_sid','active')->get();
        }

        // viar = validInternalApiRequest
        $viar = $this->reqHasApiSecret($request);
        foreach ($stocks as $stock) {
            if($viar){
                $stock->viar = true;
            }
        } 

        if($request->has('type') && $request->type == 'sku'){
            //To get stock sku wise
            return ApiResponse::success(StockSkuResource::collection($stocks));
        } else {
            return ApiResponse::success(StockResource::collection($stocks));
        }

    }

    /**
    * Create a resource
    */
    public function store(StockRequest $request){

        DB::beginTransaction();
        try{

            // $options = '[{"id": 1},{"id": 2}]';

            // $ranges = '[{"id": 1},{"id": 2},{"id": 3}]';
            
            // make an api call to ds to fetch the product along with options and ranges

            $dsFetcherObj = new DsFetcher();
            $params = $request->product_sid.'?'.$dsFetcherObj->api_secret();
            $response = $dsFetcherObj->makeApiRequest('get', '/api/products/', $params);
            $product = $response->data;

            if ($response->statusCode == 200 && $response->status == config('api.ok')) {
                $product = $response->data;
            } else {
               throw new Exception('DS:Server Error, Try again later');
            }

            $options = $product->options;
            $ranges = $product->ranges;

            foreach($options as $option){
                $product_option_id = $option->id;
                $product_option_sid = $option->sid;
                foreach($ranges as $range){
                    $product_range_id = $range->id;
                    $product_range_sid = $range->sid;
                    $sku = $product->id."-".$product_option_id."-".$product_range_id;
                    $stock = Stock::where('sku', $sku)->withTrashed()->first();
                    if($stock){
                        if($stock->trashed()){
                            $stock->restore();
                        }
                    } else {
                        Stock::create([
                            'sku' => $sku,
                            'product_id' => $product->id,
                            'product_sid' => $request->product_sid,
                            'product_option_id' => $product_option_id,
                            'product_option_sid' => $product_option_sid,
                            'product_range_id' => $product_range_id,
                            'product_range_sid' => $product_range_sid,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success('Stock created successfully.');
    }

    /**
     * here we consider given id to be a product_sid,
     * Make stock Active or Inactive by product wise
     */
    public function update(StockUpdateRequest $request, Stock $stock){
        try{
            if($request->active == 'true'){
                $active = 1;
            } else {
                $active = 0;
            }
            DB::table('stocks')->where('product_sid', $stock->product_sid)->update(['active'=> $active]);
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success('Product updated successfully');
    }

    /**
     * here we are not showing stock for given id,
     * instead we consider given id to be a product_sid,
     * and return all stock total for that product_sid
     */
    public function show(Request $request, Stock $stock )
    {
        $stock = Stock::groupBy('product_sid', 'active')->selectRaw('product_sid , active, sum(quantity) as quantity')->where('product_sid', $stock->product_sid)->first();
        
        // viar = validInternalApiRequest
        $viar = $this->reqHasApiSecret($request);
        if($viar){
            $stock->viar = true;
        }

        return ApiResponse::success(new ShowProductResource($stock));
    }

    public function getStockAddedStatus(Request $request)
    {
        $status = false;
        try{
            $stock = Stock::where('product_sid', $request->product_sid)->exists();
            if($stock){
                $status = true;
            }
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success(['status' => $status]);
    }
   
    public function deleteStock(StockRequest $request){

        // We can not delete stock if quantity of any sku of a product is greater than 0
        try{
            // even if any one result is available then we can not delete any on the related also
            $stockHasQty = Stock::where('product_sid', $request->product_sid)->where('quantity', '>', 0)->exists();

            if($stockHasQty){
                throw new Exception('You can not delete product that has stock.');
            } else {
                // delete all related stock
                Stock::where('product_sid', $request->product_sid)->delete();
            }
            
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success('Record has been deleted successfully.');
    }

    
    
}