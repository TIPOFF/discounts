<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

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

        $this->artisan('migrate', ['--database' => 'testing'])->run();

        // Create stub tables for stub models to satisfy possible FK dependencies
        foreach (config('tipoff.model_class') as $class) {
            $class::createTable();
        }
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

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('tipoff.model_class', [
            'user' => Models\User::class,
            'cart' => Models\Cart::class,
            'order' => Models\Order::class,
        ]);
        $app['config']->set('discounts.nova_class', [
            'order' => Nova\Order::class,
        ]);

        // Create stub models for anything not already defined
        foreach (config('tipoff.model_class') as $class) {
            createModelStub($class);
        }
    }
}
