<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Database\Factories;

use Tipoff\Checkout\Database\Factories\CartFactory as BaseFactory;
use Tipoff\Discounts\Tests\Support\Models\Cart;

class CartFactory extends BaseFactory
{
    protected $model = Cart::class;
}
