<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests;

use Laravel\Nova\NovaCoreServiceProvider;
use Spatie\Fractal\FractalServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Tipoff\Authorization\AuthorizationServiceProvider;
use Tipoff\Checkout\CheckoutServiceProvider;
use Tipoff\Discounts\DiscountsServiceProvider;
use Tipoff\Discounts\Tests\Support\Providers\NovaPackageServiceProvider;
use Tipoff\Statuses\StatusesServiceProvider;
use Tipoff\Support\SupportServiceProvider;
use Tipoff\TestSupport\BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NovaCoreServiceProvider::class,
            NovaPackageServiceProvider::class,
            SupportServiceProvider::class,
            AuthorizationServiceProvider::class,
            PermissionServiceProvider::class,
            StatusesServiceProvider::class,
            CheckoutServiceProvider::class,
            DiscountsServiceProvider::class,
            FractalServiceProvider::class,
        ];
    }
}
