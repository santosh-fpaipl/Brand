<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ledger_sid' => ['required', 'exists:ledgers,sid'],
            'quantities' => ['required', 'string'],
            'note' => 'nullable|string|min:1',
            'type' => 'required|in:order,ready,demand',
        ];
    }
}
