<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Discounts\DiscountsServiceProvider;
use Tipoff\Discounts\Tests\Models\Cart;
use Tipoff\Discounts\Tests\Models\Order;
use Tipoff\Discounts\Tests\Models\User;

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
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        return [
            DiscountsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('discounts.model', [
            'user' => User::class,
            'cart' => Cart::class,
            'order' => Order::class,
        ]);

        include_once __DIR__.'/../database/migrations/test/create_users_table.php.stub';
        include_once __DIR__.'/../database/migrations/test/create_carts_table.php.stub';
        include_once __DIR__.'/../database/migrations/test/create_orders_table.php.stub';
        (new \CreateUsersTable())->up();
        (new \CreateCartsTable())->up();
        (new \CreateOrdersTable())->up();

        include_once __DIR__.'/../database/migrations/create_discounts_table.php.stub';
        (new \CreateDiscountsTable())->up();
    }
}
