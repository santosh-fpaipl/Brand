<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Party;

class FabricatorTypeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $party = Party::where('sid', $value)->first();
        if ($party && $party->type !== 'fabricator') {
            $fail('The type of :attribute must be fabricator.');
        }
    }
}
