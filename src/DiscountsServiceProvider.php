<?php

declare(strict_types=1);

namespace Tipoff\Discounts;

use Illuminate\Support\Facades\Schema;
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
            ->hasTranslations()
            ->hasCommand(DiscountsCommand::class);

        if (! Schema::hasTable('discounts')) {
            $package->hasMigration('create_discounts_table');
        }
    }
}
