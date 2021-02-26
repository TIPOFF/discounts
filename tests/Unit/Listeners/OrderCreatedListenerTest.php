<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Listeners;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Discounts\Listeners\OrderCreatedListener;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\TestCase;
use Tipoff\Support\Events\Checkout\OrderCreated;
use Tipoff\TestSupport\Models\User;

class OrderCreatedListenerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function cart_discounts_are_copied_to_order()
    {
        $order = Order::factory()->create();

        /** @var Discount $discount */
        $discount = Discount::factory()->expired(false)->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $cart->order()->associate($order)->save();
        $discount->carts()->sync([$cart->id]);

        $listener = new OrderCreatedListener();
        $listener->handle(new OrderCreated($order));

        $discounts = Discount::query()->byOrderId($order->id)->get();

        $this->assertEquals(1, $discounts->count());
    }

    /** @test */
    public function auto_apply_discounts_are_copied_to_order()
    {
        $order = Order::factory()->create();

        Discount::factory()->expired(false)->autoApply()->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $cart->order()->associate($order)->save();

        $listener = new OrderCreatedListener();
        $listener->handle(new OrderCreated($order));

        $discounts = Discount::query()->byOrderId($order->id)->get();

        $this->assertEquals(1, $discounts->count());
    }
}
