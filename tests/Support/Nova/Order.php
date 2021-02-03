<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Resource;

class Order extends Resource
{
    public static $model = \Tipoff\Discounts\Tests\Support\Models\Order::class;

    public function fields(Request $request)
    {
        // TODO: Implement fields() method.
    }
}
