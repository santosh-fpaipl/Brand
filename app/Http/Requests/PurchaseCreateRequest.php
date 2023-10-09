<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class PurchaseCreateRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'purchase_order_sid' => ['required', 'string','exists:purchase_orders,sid'],
            //'quantities' => ['required', 'string'],
            'invoice_no' => ['required', 'string'],
            'invoice_date' => 'required|date_format:Y-m-d',
            'message' => ['required', 'string']
        ];
    }
}
