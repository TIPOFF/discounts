<?php

declare(strict_types=1);

namespace Tipoff\Discounts;

use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Checkout\Contracts\Models\DiscountInterface;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Policies\DiscountPolicy;

class DiscountsServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('discounts')
            ->hasConfigFile()
            ->hasTranslations();
    }

    public function registeringPackage()
    {
        $this->app->bind(DiscountInterface::class, Discount::class);

        Gate::policy(Discount::class, DiscountPolicy::class);
    }
}
