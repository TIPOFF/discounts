<?php

declare(strict_types=1);

namespace Tipoff\Discounts;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Checkout\Contracts\DiscountsService;
use Tipoff\Discounts\Commands\DiscountsCommand;
use Tipoff\Discounts\Services\DiscountsServiceImplementation;

class DiscountsServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('discounts')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommand(DiscountsCommand::class);
    }

    public function registeringPackage()
    {
        $this->app->singleton(DiscountsService::class, function () {
            return new DiscountsServiceImplementation();
        });
    }
}
