<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Http;

class StockRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'product_sid' => ['required','string'],
        ];
    }

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
        $response = Http::get(env('DS_APP') . '/api/internal/check_product', [
            'product_sid' => $this->input('product_sid'),
        ]);
        
        if ($response->status() == 200 || $response['status'] == config('api.ok')) {
            return false;  // product exist
        }

        return true; // product doesn't exists
    }
}
