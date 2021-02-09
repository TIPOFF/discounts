<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Services;

use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Contracts\DiscountsService;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\Support\Models\Cart;
use Tipoff\Discounts\Tests\TestCase;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\TestSupport\Models\User;

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

    /** @test */
    public function apply_valid_code_to_cart()
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

        $cart = Cart::factory()->create();

        $result = $api->applyCodeToCart($cart, 'TESTCODE');
        $this->assertTrue($result);

        $count = Discount::query()->byCartId($cart->id)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function apply_unknown_code_to_cart()
    {
        $api = $this->app->make(DiscountsService::class);

        $cart = Cart::factory()->create();

        $result = $api->applyCodeToCart($cart, 'TESTCODE');
        $this->assertFalse($result);

        $count = Discount::query()->byCartId($cart->id)->count();
        $this->assertEquals(0, $count);
    }

    /** @test */
    public function apply_expired_code_to_cart()
    {
        $api = $this->app->make(DiscountsService::class);

        $api->createAmountDiscount(
            'Test',
            'TESTCODE',
            Money::ofMinor(1000, 'USD'),
            AppliesTo::ORDER(),
            new Carbon('yesterday'),
            User::factory()->create()->id,
        );

        $cart = Cart::factory()->create();

        $result = $api->applyCodeToCart($cart, 'TESTCODE');
        $this->assertFalse($result);

        $count = Discount::query()->byCartId($cart->id)->count();
        $this->assertEquals(0, $count);
    }

    /** @test */
    public function apply_unsupported_code_to_cart()
    {
        $api = $this->app->make(DiscountsService::class);

        $api->createAmountDiscount(
            'Test',
            'TESTCODE',
            Money::ofMinor(1000, 'USD'),
            AppliesTo::BOOKING_AND_PRODUCT(),
            new Carbon('tomorrow'),
            User::factory()->create()->id,
        );

        $cart = Cart::factory()->create();

        $result = $api->applyCodeToCart($cart, 'TESTCODE');
        $this->assertFalse($result);

        $count = Discount::query()->byCartId($cart->id)->count();
        $this->assertEquals(0, $count);
    }

    /** @test */
    public function calculate_discount_with_no_discounts()
    {
        $api = $this->app->make(DiscountsService::class);

        $cart = Cart::factory()->create();

        $result = $api->calculateCartDiscounts($cart);
        $this->assertEquals(0, $result->getUnscaledAmount()->toInt());
    }

    /** @test */
    public function calculate_discount_with_order_discounts()
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

        $cart = Cart::factory()->create();

        $result = $api->applyCodeToCart($cart, 'TESTCODE');
        $this->assertTrue($result);

        $result = $api->calculateCartDiscounts($cart);
        $this->assertEquals(1000, $result->getUnscaledAmount()->toInt());
    }

    /** @test */
    public function calculate_discount_with_particpant_discounts()
    {
        $api = $this->app->make(DiscountsService::class);

        $api->createAmountDiscount(
            'Test',
            'TESTCODE',
            Money::ofMinor(1000, 'USD'),
            AppliesTo::PARTICIPANT(),
            new Carbon('tomorrow'),
            User::factory()->create()->id,
        );

        $cart = Cart::factory()->create();

        $result = $api->applyCodeToCart($cart, 'TESTCODE');
        $this->assertTrue($result);

        $result = $api->calculateCartDiscounts($cart);
        $this->assertEquals(4000, $result->getUnscaledAmount()->toInt());
    }

    /** @test */
    public function calculate_discount_with_multiple_discounts()
    {
        $api = $this->app->make(DiscountsService::class);

        $api->createAmountDiscount(
            'Order',
            'CODE1',
            Money::ofMinor(1000, 'USD'),
            AppliesTo::ORDER(),
            new Carbon('tomorrow'),
            User::factory()->create()->id,
        );

        $api->createAmountDiscount(
            'Participant',
            'CODE2',
            Money::ofMinor(1000, 'USD'),
            AppliesTo::PARTICIPANT(),
            new Carbon('tomorrow'),
            User::factory()->create()->id,
        );

        $cart = Cart::factory()->create();

        $result = $api->applyCodeToCart($cart, 'CODE1');
        $this->assertTrue($result);

        $result = $api->applyCodeToCart($cart, 'CODE2');
        $this->assertTrue($result);

        $result = $api->calculateCartDiscounts($cart);
        $this->assertEquals(5000, $result->getUnscaledAmount()->toInt());
    }
}
