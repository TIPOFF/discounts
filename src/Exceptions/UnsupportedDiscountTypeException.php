<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Exceptions;

use Throwable;
use Tipoff\Support\Enums\AppliesTo;

class UnsupportedDiscountTypeException extends \UnexpectedValueException implements DiscountException
{
    public function __construct(AppliesTo $type, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Discount type of {$type->getValue()} is not supported.", $code, $previous);
    }
}
