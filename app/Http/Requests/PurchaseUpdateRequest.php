<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class PurchaseUpdateRequest extends BaseRequest
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

   
    
}
