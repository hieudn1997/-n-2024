<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FundAllocation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $values)
    {
        $sum = 0;
        foreach ($values as $value) {
            $sum += $value['percentage'];
        }

        return $sum === 100;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The sum of the Fund Allocation Percentages must be 100%.';
    }
}
