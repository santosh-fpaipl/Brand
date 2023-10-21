<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\BaseRequest;
use App\Http\Fetchers\DsFetcher;
use App\Http\Fetchers\FabricatorFetcher;

class PurchaseOrderCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'fabricator_sid' => ['required', 'string'],
            'product_sid' => ['required', 'string', 'exists:stocks,product_sid'],
            'quantities' => ['required', 'string'],
            'expected_at' => 'required|after_or_equal:today|date_format:Y-m-d',
        ];
    }

    // after
    // fabricator_sid -> fab app
    // product_sid -> ds app

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->checkFabricatorExistsInFabricatorApp()) {
                    $validator->errors()->add(
                        'fabricator_sid',
                        'Invalid Fabricator'
                    );
                }
                if ($this->checkProductExistsInDesignStudioApp()) {
                    $validator->errors()->add(
                        'product_sid',
                        'Invalid product'
                    );
                }
            }
        ];
    }

    private function checkFabricatorExistsInFabricatorApp()
    {
        $fabricatorFetcherrObj = new FabricatorFetcher();
        $params = $this->input('fabricator_sid').'?'.$fabricatorFetcherrObj->api_secret().'&&check=available';
        $response = $fabricatorFetcherrObj->makeApiRequest('get', '/api/fabricators/', $params);
        if ($response->statusCode == 200 && $response->status == config('api.ok')) {
            return false;  // product exist
        }
        return true; // product doesn't exists
    }


    private function checkProductExistsInDesignStudioApp()
    {
        $dsFetcherObj = new DsFetcher();
        $params = $this->input('product_sid').'?'.$dsFetcherObj->api_secret().'&&check=available';
        $response = $dsFetcherObj->makeApiRequest('get', '/api/products/', $params);
        if ($response->statusCode == 200 && $response->status == config('api.ok')) {
            return false;  // product exist
        }
        return true; // product doesn't exists
    }
}