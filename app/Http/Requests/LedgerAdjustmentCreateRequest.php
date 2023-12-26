<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CheckAdjustmentFieldRule;

class LedgerAdjustmentCreateRequest extends FormRequest
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
            'ledger_sid' => ['required', 'exists:ledgers,sid'],
            'order_qty' => 'nullable|integer|min:1',
            'ready_qty' => 'nullable|integer|min:1',
            'demand_qty' => 'nullable|integer|min:1',
        ];

        // $hasIntegerValue = collect($validatedData)
        //     ->except('ledger_sid') // Exclude ledger_sid from check
        //     ->filter(function ($value) {
        //         return is_numeric($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
        //     })
        //     ->count() > 0;

        // if (!$hasIntegerValue) {
        //     return response()->json(['error' => 'At least one of the fields must have an integer value.'], 422);
        // }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasIntegerValue = collect($this->validated())->except('ledger_sid')
                ->filter(function ($value) {
                    return is_numeric($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
                })
                ->count() > 0;

            if (!$hasIntegerValue) {
                $validator->errors()->add('integer_validation', 'At least one of the fields must have an integer value.');
            }
        });
    }
}
