<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Contracts;

use Brick\Money\Money;
use Carbon\Carbon;
use Tipoff\Support\Enums\AppliesTo;

interface DiscountsService
{
    public function createAmountDiscount(string $name, string $code, Money $amount, AppliesTo $appliesTo, Carbon $expiresAt, int $creatorId);

    public function createPercentDiscount(string $name, string $code, float $percent, AppliesTo $appliesTo, Carbon $expiresAt, int $creatorId);

    public function applyCodeToCart(DiscountableCart $cart, string $code): bool;

    public function calculateCartDiscounts(DiscountableCart $cart): Money;
}
