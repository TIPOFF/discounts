<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\API;

use Assert\LazyAssertionException;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Contracts\DiscountsService;
use Tipoff\Discounts\Enums\AppliesTo;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\Support\Models\Cart;
use Tipoff\Discounts\Tests\Support\Models\Order;
use Tipoff\Discounts\Tests\Support\Models\User;
use Tipoff\Discounts\Tests\TestCase;

class DiscountsServiceImplementationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create_amount_discount()
    {
        $api = $this->app->make(DiscountsService::class);

        $api->createAmountDiscount(
            'Test',
            'TESTCODE',
            Money::ofMinor(1000, 'USD'),
            AppliesTo::ORDER(),
            new Carbon('tomorrow'),
            User::factory()->create()->id,
        );

        $count = Discount::query()->where('code', 'TESTCODE')->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function create_percent_discount()
    {
        $api = $this->app->make(DiscountsService::class);

        $api->createPercentDiscount(
            'Test',
            'TESTCODE',
            0.12,
            AppliesTo::ORDER(),
            new Carbon('tomorrow'),
            User::factory()->create()->id,
        );

        $count = Discount::query()->where('code', 'TESTCODE')->count();
        $this->assertEquals(1, $count);
    }
}
