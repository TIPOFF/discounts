<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

use Illuminate\Support\Facades\Schema;
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
            // TODO - push existence check into tipoff/support trait
            if (!Schema::hasTable((new $class)->getTable())) {
                $class::createTable();
            }
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

    private function stubModel(string $class): void
    {
        if (class_exists($class)) {
            return;
        }

        $classBasename = class_basename($class);
        $classNamespace = substr($class, 0, strrpos($class, '\\'));

        $classDef = <<<EOT
namespace {$classNamespace};

use Illuminate\Database\Eloquent\Model;
use Tipoff\Support\Models\TestModelStub;

class {$classBasename} extends Model {
    use TestModelStub;

    protected \$guarded = [
        'id',
    ];
};
EOT;
        // alias the anonymous class with your class name
        eval($classDef);
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
            $this->stubModel($class);
        }
    }
}
