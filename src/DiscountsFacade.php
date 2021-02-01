<?php

declare(strict_types=1);

namespace Tipoff\Discounts;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tipoff\Discounts\Discounts
 */
class DiscountsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'discounts';
    }
}
