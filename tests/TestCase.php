<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Discounts\DiscountsServiceProvider;

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
        include_once __DIR__.'/../database/migrations/create_discounts_table.php.stub';
        (new \CreateDiscountsTable())->up();
    }
}
