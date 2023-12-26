<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\BaseRequest;
use App\Http\Fetchers\DsFetcher;

class OrderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'party_sid' => ['required', 'string', 'exists:parties,sid'],
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
                
                if ($this->checkProductExistsInDesignStudioApp()) {
                    $validator->errors()->add(
                        'product_sid',
                        'Invalid product'
                    );
                }
            }
        ];
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
