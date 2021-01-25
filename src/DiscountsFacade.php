<?php

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
