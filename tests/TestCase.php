<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Nova\NovaCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Checkout\CheckoutServiceProvider;
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

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $baseName = class_basename($modelName);
            foreach ([
                         'Discounts',
                         'Checkout',
                     ] as $package) {
                $factoryClass = "Tipoff\\{$package}\\Database\\Factories\\{$baseName}Factory";
                if (class_exists($factoryClass)) {
                    return $factoryClass;
                }
            }
        });

        $this->artisan('migrate', ['--database' => 'testing'])->run();

        // Create stub tables to satisfy FK dependencies
        foreach (config('checkout.model_class') as $class) {
            $class::createTable();
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            NovaCoreServiceProvider::class,
            NovaTestbenchServiceProvider::class,
            SupportServiceProvider::class,
            CheckoutServiceProvider::class,
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
        $app['config']->set('discounts.model_class', [
            'user' => Models\User::class,
        ]);

        $app['config']->set('discounts.nova_class', [
            'order' => Nova\Order::class,
        ]);

        foreach (config('checkout.model_class') as $class) {
            $this->stubModel($class);
        }
    }
}
