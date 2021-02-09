<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Providers;

use Tipoff\Discounts\Nova\Discount;
use Tipoff\TestSupport\Providers\BaseNovaPackageServiceProvider;

class NovaPackageServiceProvider extends BaseNovaPackageServiceProvider
{
    public static array $packageResources = [
        Discount::class,
    ];
}
