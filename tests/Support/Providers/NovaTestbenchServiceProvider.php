<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Tipoff\Discounts\Nova\Discount;

class NovaTestbenchServiceProvider extends NovaApplicationServiceProvider
{
    protected function resources()
    {
        Nova::resources([
            Discount::class,
        ]);
    }

    protected function routes()
    {
        Nova::routes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return true;
        });
    }
}
