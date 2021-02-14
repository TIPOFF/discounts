<?php

declare(strict_types=1);

namespace Tipoff\Discounts;

use Tipoff\Checkout\Contracts\Models\DiscountInterface;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Policies\DiscountPolicy;
use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;

class DiscountsServiceProvider extends TipoffServiceProvider
{
    public function configureTipoffPackage(TipoffPackage $package): void
    {
        /**
         * Tipoff specific methods should precede base methods to avoid Psalm error	
         */
        $package
            ->hasModelInterfaces([
                DiscountInterface::class => Discount::class,	
            ])
            ->hasPolicies([
                Discount::class => DiscountPolicy::class,
            ])
            ->name('discounts')
            ->hasConfigFile();
    }
}
