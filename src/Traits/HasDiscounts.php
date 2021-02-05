<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Traits;

use Assert\Assert;
use Brick\Money\Money;
use Tipoff\Checkout\Contracts\CartInterface;
use Tipoff\Checkout\Contracts\DiscountsService;

trait HasDiscounts
{
    public function applyDiscountCode(string $discountCode): CartInterface
    {
        $cart = $this->getCartInterface();
        if (app(DiscountsService::class)->applyCodeToCart($cart, $discountCode)) {
            $cart->updateTotalCartDeductions();

            return $cart;
        }

        throw new \Exception("Code {$discountCode} is invalid.");
    }

    public function calculateDiscountsTotal(): Money
    {
        return app(DiscountsService::class)->calculateDeductions($this->getCartInterface());
    }

    protected function getCartInterface(): CartInterface
    {
        Assert::that($this)->isInstanceOf(CartInterface::class);

        return $this;
    }
}
