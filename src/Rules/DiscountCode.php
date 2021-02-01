<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Rules;

use Illuminate\Contracts\Validation\Rule;

class DiscountCode implements Rule
{
    private const ILLEGAL_LENGTH = 9;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return strlen((string) $value) !== self::ILLEGAL_LENGTH;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.discount_code');
    }
}
