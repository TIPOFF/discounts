<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Nova\NovaCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Discounts\DiscountsServiceProvider;
use Tipoff\Discounts\Tests\Support\Models;
use Tipoff\Discounts\Tests\Support\Nova;
use Tipoff\Discounts\Tests\Support\Providers\NovaTestbenchServiceProvider;
use Tipoff\Support\SupportServiceProvider;

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
            NovaCoreServiceProvider::class,
            NovaTestbenchServiceProvider::class,
            SupportServiceProvider::class,
            DiscountsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('discounts.model_class', [
            'user' => Models\User::class,
            'cart' => Models\Cart::class,
            'order' => Models\Order::class,
        ]);
        $app['config']->set('discounts.nova_class', [
            'order' => Nova\Order::class,
        ]);

        // Create stub tables to satisfy FK dependencies
        foreach (config('discounts.model_class') as $class) {
            $class::createTable();
        }

        include_once __DIR__.'/../database/migrations/2020_05_06_110000_create_discounts_table.php';
        include_once __DIR__.'/../database/migrations/2020_05_06_120000_create_discount_order_table.php';
        include_once __DIR__.'/../database/migrations/2020_06_30_110000_create_cart_discount_pivot_table.php';
        (new \CreateDiscountsTable())->up();
        (new \CreateDiscountOrderTable())->up();
        (new \CreateCartDiscountPivotTable())->up();
    }
}
