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
use Exception;

class StockProviderPartyId extends Provider
{
    public function index(Request $request)
    {
        // First check the request type. i.e. Productwise or Skuwise
        if($request->has('type') && $request->type == 'sku'){
            // Skuwise
            $stocks = Stock::all();
            $stockResponse = StockSkuResource::collection($stocks);
        } else {
            // Productwise
            $stocks = Stock::select('product_sid', DB::raw('SUM(quantity) as quantity'),'active')->groupBy('product_sid','active')->get();
            //$stocks = Stock::all();
            return StockResource::collection($stocks);
        }


        //$sortedStockData = $stockResponse->collection->sortByDesc('idx');

        // $sortedStockData = $stockResponse->sort(function ($a, $b) {
        //     return $b['id'] - $a['id'];
        // });

        $sortedData = $stockResponse->collection->toArray();
        usort($sortedData, function ($a, $b) {
            echo "</pre>";
            print_r($a);
            print_r($b);
            exit;
           // return $b['id'] <=> $a['id'];
        });

        // // Convert the sorted array back to a collection if needed
        // $sortedStockData = collect($sortedData);

        // $sortedResources = $stockResponse->collection->sortBy([
        //     ['id', 'desc'],
        // ]);
      
        
        return ApiResponse::success($sortedStockData);
    }

    public function store(StockRequest $request)
    {
        DB::beginTransaction();

        try{

            // $options = '[{"id": 1},{"id": 2}]';
            // $ranges = '[{"id": 1},{"id": 2},{"id": 3}]';
            // make an api call to ds to fetch the product along with options and ranges

            $dsFetcherObj = new DsFetcher();
            $params = $request->product_sid.'?'.$dsFetcherObj->api_secret();
            $response = $dsFetcherObj->makeApiRequest('get', '/api/products/', $params);
            if ($response->statusCode == 200 && $response->status == config('api.ok')) {
                $product = $response->data;
                $productId = $product->id;
            } else {
               throw new Exception('DS:Server Error, Try again later');
            }

            $productOptions = $product->options;
            $productRanges = $product->ranges;

            foreach($productOptions as $option){
                $productOptionId = $option->id;
                $productOptionSid = $option->sid;
                foreach($productRanges as $range){
                    $productRangeId = $range->id;
                    $productRangeSid = $range->sid;
                    $skuId = $productId."-".$productOptionId."-".$productRangeId;
                    $stock = Stock::where('sku', $skuId)->withTrashed()->first();
                    if($stock){
                        if($stock->trashed()){
                            $stock->restore();
                        }
                    } else {
                        Stock::create([
                            'sku' => $skuId,
                            'product_id' => $productId,
                            'product_sid' => $request->product_sid,
                            'product_option_id' => $productOptionId,
                            'product_option_sid' => $productOptionSid,
                            'product_range_id' => $productRangeId,
                            'product_range_sid' => $productRangeSid,
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
    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'query_type' => 'required|in:toggle_active',
            'value' => 'required|boolean'
        ]);
        
        try{
            switch ($request->query_type) {
                case 'toggle_active':
                    $result = $stock->skus()->update(['active' => $request->value]);
                    break;
                
                default: break;
            }
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }

        return ApiResponse::success([
            $request->query_type => $result
        ]);
    }

    /**
     * here we are not showing stock for given id,
     * instead we consider given id to be a product_sid,
     * and return all stock total for that product_sid
     */
    public function show(Request $request, Stock $stock )
    {
        // $stock = Stock::groupBy('product_sid', 'active')->selectRaw('product_sid , active, sum(quantity) as quantity')->where('product_sid', $stock->product_sid)->first();
        return ApiResponse::success(new ShowProductResource($stock));
    }

    public function query(StockRequest $request)
    {
        $request->validate([
            'query_type' => 'required|in:exists,stock,stock_sku,sku_count',
            'sku_id' => 'required_if:query_type,stock_sku|exists:stocks,sku'
        ]);
        
        try{
            switch ($request->query_type) {
                case 'exists':
                    $result = Stock::where('product_sid', $request->product_sid)->exists();
                    break;
                
                case 'stock_sku':
                    $result = Stock::where('product_sid', $request->product_sid)->where('sku', $request->sku_id)->firstOrFail()->quantity;
                    break;

                case 'stock':
                    $result = Stock::where('product_sid', $request->product_sid)->sum('quantity');
                    break;

                case 'sku_count':
                    $result = Stock::where('product_sid', $request->product_sid)->count();
                    break;

                default: break;
            }
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }

        return ApiResponse::success([
            $request->query_type => $result
        ]);
    }
   
    public function delete(StockRequest $request)
    {
        // We can not delete stock if quantity of any sku of a product is greater than 0
        try{
            // even if any one result is available then we can not delete any on the related also
            $stockHasQty = Stock::where('product_sid', $request->product_sid)->sum('quantity') > 0;

            if($stockHasQty){
                throw new Exception('You can not delete product that has stock.');
            } else {
                // delete all stock sku's
                Stock::where('product_sid', $request->product_sid)->delete();
            }
            
        } catch(\Exception $e){
            return ApiResponse::error($e->getMessage(), 404);
        }
        return ApiResponse::success('Record has been deleted successfully.');
    }
}