<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Services;

use Brick\Money\Money;
use Carbon\Carbon;
use Tipoff\Discounts\Contracts\DiscountableCart;
use Tipoff\Discounts\Contracts\DiscountsService;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Support\Enums\AppliesTo;

class DiscountsServiceImplementation implements DiscountsService
{
    public function createAmountDiscount(string $name, string $code, Money $amount, AppliesTo $appliesTo, Carbon $expiresAt, int $creatorId)
    {
        Discount::create([
            'name' => $name,
            'code' => $code,
            'amount' => $amount->getUnscaledAmount()->toInt(),
            'percent' => null,
            'applies_to' => $appliesTo,
            'expires_at' => $expiresAt,
            'creator_id' => $creatorId,
            'updater_id' => $creatorId,
        ]);
    }

    public function createPercentDiscount(string $name, string $code, float $percent, AppliesTo $appliesTo, Carbon $expiresAt, int $creatorId)
    {
        Discount::create([
            'name' => $name,
            'code' => $code,
            'amount' => null,
            'percent' => $percent,
            'applies_to' => $appliesTo,
            'expires_at' => $expiresAt,
            'creator_id' => $creatorId,
            'updater_id' => $creatorId,
        ]);
    }

    public function applyCodeToCart(DiscountableCart $cart, string $code): bool
    {
        /** @var Discount $discount */
        if ($discount = Discount::query()->available()->where('code', $code)->first()) {

            // Check for supported discount type
            if (in_array($discount->applies_to->getValue(), array_keys(config('discounts.applications')))) {
                $discount->carts()->syncWithoutDetaching([$cart->getId()]);

                return true;
            }
        }

        return false;
    }

    public function calculateCartDiscounts(DiscountableCart $cart): Money
    {
        $discounts = Discount::query()->byCartId($cart->getId())->get();

        return $discounts->reduce(function (Money $total, Discount $discount) use ($cart) {
            $amount = Money::ofMinor($discount->amount, 'USD');

            if ($amount->isPositive()) {
                switch ($discount->applies_to) {
                    case AppliesTo::ORDER():
                        $total = $total->plus($amount);

                        break;
                    case AppliesTo::PARTICIPANT():
                        $total = $total->plus($amount->multipliedBy($cart->getTotalParticipants()));

                        break;
                }
            }

            return $total;
        }, Money::ofMinor(0, 'USD'));
    }
}
