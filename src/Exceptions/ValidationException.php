<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Exceptions;

class ValidationException extends \Illuminate\Validation\ValidationException implements DiscountException
{
}
