<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Models;

use Tipoff\Checkout\Models\Cart as BaseCart;
use Tipoff\Discounts\Traits\HasDiscounts;

class Cart extends BaseCart
{
    use HasDiscounts;
}
