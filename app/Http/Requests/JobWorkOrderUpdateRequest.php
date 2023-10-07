<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class JobWorkOrderUpdateRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'message' => [$this->getMessageValidationRule(), 'string'],
            'status' => [$this->getStatusValidationRule(),'in:next,cancelled'],
            'quantities' => [$this->getQuantitiesValidationRule(), 'string'],
            'quantities.*' => ['integer', 'min:1'],
            'expected_at' => [$this->getExpectedAtValidationRule(),'date_format:Y-m-d'],
        ];
    }

    /**
    * Get the validation rule for the 'status' field based on the provided inputs.
    *
    * @return array|string
    */

    private function getMessageValidationRule()
    {
        if ($this->has('message')) {
            return 'required';
        } else {
            return 'nullable';
        }
    }
    

    private function getStatusValidationRule()
    {
        if ($this->has('status')) {
            return 'required';
        } else {
            return 'nullable';
        }
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