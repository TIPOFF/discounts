<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Feature\Nova;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Router;
use Tipoff\Discounts\Tests\TestCase;

class DiscountResourceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create_amount_discount()
    {
        $this->markTestSkipped('Still trying to figure out how to get Resource routes registered in the testbench');

        /** @var Router $router */
        $router = $this->app->make('router');
        dd($router->getRoutes());
        // dd($router);
        $json = $this->getJson("nova/password/reset");
        dump($json);
    }
}
