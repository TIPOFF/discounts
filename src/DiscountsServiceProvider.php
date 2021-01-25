<?php

namespace Tipoff\Discounts;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Discounts\Commands\DiscountsCommand;

class DiscountsServiceProvider extends PackageServiceProvider
{
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
            ->hasViews()
            ->hasMigration('create_discounts_table')
            ->hasCommand(DiscountsCommand::class);
    }
}
