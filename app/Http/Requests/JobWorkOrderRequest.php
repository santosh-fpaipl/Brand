<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class JobWorkOrderRequest extends BaseRequest
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
            'product_sid' => ['required', 'string'],
            'quantities' => ['required', 'string'],
            //'quantities.*' => ['json'],
            'delivery_date' => 'required|date_format:Y-m-d',
        ];
    }
}