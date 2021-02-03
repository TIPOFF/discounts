<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Discounts\DiscountsServiceProvider;
use Tipoff\Discounts\Tests\Models\Cart;
use Tipoff\Discounts\Tests\Models\Order;
use Tipoff\Discounts\Tests\Models\User;
use Tipoff\Discounts\Tests\Support\NovaServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Tipoff\\Discounts\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            NovaServiceProvider::class,
            DiscountsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('discounts.model_class', [
            'user' => User::class,
            'cart' => Cart::class,
            'order' => Order::class,
        ]);

        include_once __DIR__.'/../database/migrations/test/create_users_table.php';
        include_once __DIR__.'/../database/migrations/test/create_carts_table.php';
        include_once __DIR__.'/../database/migrations/test/create_orders_table.php';
        (new \CreateUsersTable())->up();
        (new \CreateCartsTable())->up();
        (new \CreateOrdersTable())->up();

        include_once __DIR__.'/../database/migrations/2020_05_06_110000_create_discounts_table.php';
        include_once __DIR__.'/../database/migrations/2020_05_06_120000_create_discount_order_table.php';
        include_once __DIR__.'/../database/migrations/2020_06_30_110000_create_cart_discount_pivot_table.php';
        (new \CreateDiscountsTable())->up();
        (new \CreateDiscountOrderTable())->up();
        (new \CreateCartDiscountPivotTable())->up();
    }
}
