<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Discounts\Tests\Support\Models\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
        ];
    }
}
