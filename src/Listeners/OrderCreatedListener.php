<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Listeners;

use Illuminate\Support\Collection;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Support\Events\Checkout\OrderCreated;

class OrderCreatedListener
{
    public function handle(OrderCreated $event): void
    {
        /** @var Order $order */
        $order = $event->order;
        $discounts = $this->getDiscountsApplied($order->cart);
        $this->copyDiscountsToOrder($order, $discounts);
    }

    private function getDiscountsApplied(Cart $cart): Collection
    {
        return Discount::query()->byCartId($cart->getId(), true)->get();
    }

    private function copyDiscountsToOrder(Order $order, Collection $discounts): self
    {
        $order->discounts()->sync($discounts->pluck('id')->toArray());

        return $this;
    }
}
