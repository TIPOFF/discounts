<?php

declare(strict_types=1);

namespace Tipoff\Discounts;

use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Policies\DiscountPolicy;
use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;

class DiscountsServiceProvider extends TipoffServiceProvider
{
    public function configureTipoffPackage(TipoffPackage $package): void
    {
        $package
            ->hasPolicies([
                Discount::class => DiscountPolicy::class,
            ])
            ->name('discounts')
            ->hasConfigFile();
    }
}
