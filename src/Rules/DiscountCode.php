<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Rules;

use Illuminate\Contracts\Validation\Rule;

class DiscountCode implements Rule
{
    private const ILLEGAL_LENGTH = 9;

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        return strlen((string) $value) !== self::ILLEGAL_LENGTH;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return (string) 'A discount code cannot be 9 characters long.';
    }
}
