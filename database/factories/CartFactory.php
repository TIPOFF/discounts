<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Discounts\Tests\Support\Models\Cart;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition()
    {
        return [
        ];
    }
}
