<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class PurchaseOrderUpdateRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['nullable','string','min:1'],
            'status' => ['nullable','in:next,cancelled'],
            'quantities' => [$this->getQuantitiesValidationRule(), 'string'],
            'quantities.*' => ['integer', 'min:1'],
            'expected_at' => [$this->getExpectedAtValidationRule(),'after_or_equal:today','date_format:Y-m-d'],
        ];
    }

    private function getQuantitiesValidationRule()
    {
        if ($this->has('quantities')) {
            return 'required';
        } else {
            return 'nullable';
        }
    }

    private function getExpectedAtValidationRule()
    {
        if ($this->has('expected_at')) {
            return 'required';
        } else {
            return 'nullable';
        }
    }
    
}