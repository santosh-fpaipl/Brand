<?php
namespace App\Http\Providers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Http\Providers\Provider;
use App\Http\Responses\ApiResponse;
use App\Models\Purchase;
use App\Http\Resources\SaleResource;


class SaleProvider extends Provider
{
    
    /**
     * Display a listing of the resource.
     */
    public function sales(Request $request)
    {
        $sales = Purchase::select(
            DB::raw('SUM(quantity) as total'), 
            //DB::raw('product_sid as product_sid'),
            DB::raw('DATE_FORMAT(created_at, "%M") as month')
        )
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%M")'))
           // ->groupBy(DB::raw('product_sid'))
            ->get();
        
        $datas = [
            'legend' => 'Sale purchase financial report of FY 22-23',
            'titles' => [
                'Units', // Y axis
                'Months' // X axis
            ],
            'labels' => [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December"
            ],
            'datasets' => [ 
                [
                    'label' => 'Sale',
                    'data' => [],
                ],
                [
                    'label' => 'Purchase',
                    'data' => [],
                ]
            ],
        ];

        // foreach($datas['labels'] as $v){
        //     $datasets = [
        //         'label' => '# of Votes',
        //         'data' => [0],
        //         'borderWidth' => 2,
        //         'backgroundColor' => ['red']
        //     ];
        //     foreach($sales as $sale){
        //         if(strtolower($sale->month) === strtolower($v)){
        //             $datasets['data'] = $sale->total;

        //         }
        //     }
        //     array_push($datas['datasets'], $datasets);
        // }


        foreach($datas['labels'] as $month){
            $total = 0;
            foreach($sales as $sale){
                if(strtolower($sale->month) === strtolower($month)){
                    $total = (int)$sale->total;
                    break;
                }
            }
            array_push($datas['datasets'][0]['data'], $total);

            array_push($datas['datasets'][1]['data'], $total * 1.5);
        }

        

        return ApiResponse::success(new SaleResource($datas));
    }

    
    
}