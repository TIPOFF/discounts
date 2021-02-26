<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Models;

use Assert\LazyAssertionException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Discounts\Exceptions\UnsupportedDiscountTypeException;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\TestCase;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\TestSupport\Models\User;

class DiscountModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create_amount_discount()
    {
        $discount = Discount::factory()->amount()->create();
        $this->assertNotNull($discount);
    }

    /** @test */
    public function create_percent_discount()
    {
        $discount = Discount::factory()->percent()->create();
        $this->assertNotNull($discount);
    }

    /** @test */
    public function creator_and_updater_are_set()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $discount = Discount::factory()->create([
            'creator_id' => null,
            'updater_id' => null,
        ]);

        $this->assertNotNull($discount->creator_id);
        $this->assertEquals($creator->id, $discount->creator_id);
        $this->assertInstanceOf(Model::class, $discount->creator);

        $this->assertNotNull($discount->updater_id);
        $this->assertEquals($creator->id, $discount->updater_id);
        $this->assertInstanceOf(Model::class, $discount->updater);
    }

    /** @test */
    public function updater_is_set()
    {
        $creator = User::factory()->create();
        $discount = Discount::factory()->create([
            'code' => 'ABCD',
            'creator_id' => $creator,
            'updater_id' => $creator,
        ]);

        $updater = User::factory()->create();
        $this->assertNotEquals($creator->id, $updater->id);

        $this->actingAs($updater);

        $discount->code = 'HIJK';
        $discount->save();

        $this->assertEquals($updater->id, $discount->updater_id);
        $this->assertInstanceOf(Model::class, $discount->updater);
    }

    /** @test */
    public function discount_code_is_normalized()
    {
        $discount = Discount::factory()->create([
            'code' => 'abcd',
        ]);

        $this->assertEquals('ABCD', $discount->code);

        $discount->code = 'hijk';
        $discount->save();

        $this->assertEquals('HIJK', $discount->code);
    }

    /** @test */
    public function max_usage_is_normalized()
    {
        $discount = Discount::factory()->create([
            'max_usage' => null,
        ]);

        $this->assertEquals(1, $discount->max_usage);
    }

    /** @test */
    public function expiration_is_detected()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->expired()->create();

        $this->assertFalse($discount->isValidAt('today'));

        /** @var Discount $discount */
        $discount = Discount::factory()->expired(false)->create();

        $this->assertTrue($discount->isValidAt('today'));
    }

    /** @test */
    public function scope_by_valid_at()
    {
        $today = new Carbon('today');

        Discount::factory()->expired()->count(3)->create();

        $count = Discount::query()->validAt($today)->count();
        $this->assertEquals(0, $count);

        Discount::factory()->expired(false)->count(4)->create();

        $count = Discount::query()->validAt($today)->count();
        $this->assertEquals(4, $count);
    }

    /** @test */
    public function scope_by_available()
    {
        Discount::factory()->expired()->count(3)->create();

        $count = Discount::query()->available()->count();
        $this->assertEquals(0, $count);

        Discount::factory()->expired(false)->count(4)->create();

        $count = Discount::query()->available()->count();
        $this->assertEquals(4, $count);
    }

    /** @test */
    public function scope_is_auto_apply()
    {
        Discount::factory()->expired(false)->autoApply(false)->count(1)->create();

        $count = Discount::query()->isActiveAutoApply()->count();
        $this->assertEquals(0, $count);

        Discount::factory()->expired()->autoApply()->count(1)->create();

        $count = Discount::query()->isActiveAutoApply()->count();
        $this->assertEquals(0, $count);

        Discount::factory()->expired(false)->autoApply()->count(4)->create();

        $count = Discount::query()->isActiveAutoApply()->count();
        $this->assertEquals(4, $count);
    }

    /** @test */
    public function cart_relation()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $discount->carts()->sync([$cart->id]);

        $discount->refresh();
        $this->assertEquals(1, $discount->carts()->count());
    }

    /** @test */
    public function by_cart_id()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $discount->carts()->sync([$cart->id]);

        $discounts = Discount::query()->byCartId($cart->id)->get();

        $this->assertEquals(1, $discounts->count());
    }

    /** @test */
    public function order_relation()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->create();

        $order = Order::factory()->create();
        $discount->orders()->sync([$order->id]);

        $discount->refresh();
        $this->assertEquals(1, $discount->orders()->count());
    }

    /** @test */
    public function by_order_id()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->create();

        $order = Order::factory()->create();
        $discount->orders()->sync([$order->id]);

        $discounts = Discount::query()->byOrderId($order->id)->get();

        $this->assertEquals(1, $discounts->count());
    }

    /** @test */
    public function code_length_not_nine()
    {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('code: Value "9" was not expected to be equal to value "9"');

        Discount::factory()->create([
            'code' => '123456789',
        ]);
    }

    /** @test */
    public function missing_amount_and_percent()
    {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('amount: A discount must have either an amount or percent.');

        Discount::factory()->create([
            'amount' => null,
            'percent' => null,
        ]);
    }

    /** @test */
    public function both_amount_and_percent()
    {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('amount: A discount cannot have both an amount & percent.');

        Discount::factory()->create([
            'amount' => 1000,
            'percent' => 0.12,
        ]);
    }

    /** @test */
    public function find_valid_code()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->amount(1000)->expired(false)->create([
            'code' => 'TESTCODE',
            'applies_to' => AppliesTo::ORDER(),
        ]);

        $result = Discount::findByCode('TESTCODE');
        $this->assertNotNull($result);
        $this->assertEquals($discount->id, $result->getId());
    }

    /** @test */
    public function apply_code_to_cart()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->amount(1000)->expired(false)->create([
            'code' => 'TESTCODE',
            'applies_to' => AppliesTo::ORDER(),
        ]);

        $cart = Cart::factory()->create();

        $discount->applyToCart($cart);

        $count = Discount::query()->byCartId($cart->id)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function find_unknown_code()
    {
        $result = Discount::findByCode('TESTCODE');
        $this->assertNull($result);
    }

    /** @test */
    public function find_expired_code()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->amount(1000)->expired(true)->create([
            'code' => 'TESTCODE',
            'applies_to' => AppliesTo::ORDER(),
        ]);

        $result = Discount::findByCode('TESTCODE');
        $this->assertNull($result);
    }

    /** @test */
    public function apply_unsupported_code_to_cart()
    {
        /** @var Discount $discount */
        $discount = Discount::factory()->amount(1000)->expired(false)->create([
            'code' => 'TESTCODE',
            'applies_to' => AppliesTo::BOOKING_AND_PRODUCT(),
        ]);

        $cart = Cart::factory()->create();

        $this->expectException(UnsupportedDiscountTypeException::class);
        $this->expectExceptionMessage('Discount type of each is not supported');

        $discount->applyToCart($cart);
    }

    /** @test */
    public function get_codes_for_cart()
    {
        /** @var Discount $orderCode */
        $orderCode = Discount::factory()->amount(1000)->expired(false)->create([
            'code' => 'CODE1',
            'applies_to' => AppliesTo::ORDER(),
        ]);

        /** @var Discount $participantCode */
        $participantCode = Discount::factory()->amount(1000)->expired(false)->create([
            'code' => 'CODE2',
            'applies_to' => AppliesTo::PARTICIPANT(),
        ]);

        $cart = Cart::factory()->create();

        $orderCode->applyToCart($cart);
        $participantCode->applyToCart($cart);

        $codes = Discount::getCodesForCart($cart);

        $this->assertCount(2, $codes);
        $this->assertEquals(['CODE1', 'CODE2'], $codes);
    }
}
