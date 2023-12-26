<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DemandUpdateRequest extends FormRequest
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
            'expected_at' => [$this->getExpectedAtValidationRule(),'after_or_equal:today','date_format:Y-m-d'],
        ];
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
