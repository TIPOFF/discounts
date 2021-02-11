<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Models;

use Assert\Assert;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Tipoff\Checkout\Contracts\Models\CartDeduction;
use Tipoff\Checkout\Contracts\Models\CartInterface;
use Tipoff\Checkout\Contracts\Models\DiscountInterface;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Discounts\Exceptions\UnsupportedDiscountTypeException;
use Tipoff\Support\Casts\Enum;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasCreator;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Support\Traits\HasUpdater;

/**
 * @property int|null id
 * @property string name
 * @property string code
 * @property int amount
 * @property float percent
 * @property AppliesTo applies_to
 * @property int max_usage
 * @property bool auto_apply
 * @property Carbon expires_at
 * // Raw Relations
 * @property int|null creator_id
 * @property int|null updater_id
 */
class Discount extends BaseModel implements DiscountInterface
{
    use HasPackageFactory;
    use HasCreator;
    use HasUpdater;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'code' => 'string', // TODO - use custom class to represent DiscountCode?
        'amount' => 'integer',
        'percent' => 'float',
        'applies_to' => Enum::class.':'.AppliesTo::class,
        'max_usage' => 'integer',
        'auto_apply' => 'boolean',
        'expires_at' => 'datetime',
        'creator_id' => 'integer',
        'updater_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Discount $discount) {
            $discount->code = strtoupper($discount->code);
            if (empty($discount->max_usage)) {
                $discount->max_usage = 1;
            }

            Assert::lazy()
                ->that(strlen($discount->code), 'code')->notEq(9)
                ->that(empty($discount->amount) && empty($discount->percent), 'amount')->false('A discount must have either an amount or percent.')
                ->that(! empty($discount->amount) && ! empty($discount->percent), 'amount')->false('A discount cannot have both an amount & percent.')
                ->verifyNow();
        });
    }

    /**
     * Scope discounts to valid ones.
     *
     * @param Builder $query
     * @param string|Carbon $date
     * @return Builder
     */
    public function scopeValidAt(Builder $query, $date): Builder
    {
        return $query
            ->whereDate('expires_at', '>=', $date);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $this->scopeValidAt($query, new Carbon('now'));
    }

    public function scopeByCartId(Builder $query, int $cartId): Builder
    {
        return $query->whereHas('carts', function ($q) use ($cartId) {
            $q->where('id', $cartId);
        });
    }

    public function scopeByOrderId(Builder $query, int $orderId): Builder
    {
        return $query->whereHas('orders', function ($q) use ($orderId) {
            $q->where('id', $orderId);
        });
    }

    /**
     * Validate is current discount is available at specified date.
     *
     * @param string|Carbon $date
     * @return bool
     */
    public function isValidAt($date): bool
    {
        if (! $date instanceof Carbon) {
            $date = new Carbon($date);
        }

        if ($date->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }

    /******************************
     * DiscountInterface Implementation
     ******************************/

    public static function findDeductionByCode(string $code): ?CartDeduction
    {
        return Discount::query()->available()->where('code', $code)->first();
    }

    public static function calculateCartDeduction(CartInterface $cart): Money
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

    public static function markCartDeductionsAsUsed(CartInterface $cart): void
    {
        // Does not apply to discount codes
    }

    public function applyToCart(CartInterface $cart)
    {
        // Check for supported discount type
        if (in_array($this->applies_to->getValue(), array_keys(config('discounts.applications')))) {
            $this->carts()->syncWithoutDetaching([$cart->getId()]);

            return;
        }

        throw new UnsupportedDiscountTypeException($this->applies_to);
    }

    public function getCodesForCart(CartInterface $cart): array
    {
        return Discount::query()->byCartId($cart->getId())->pluck('code')->toArray();
    }
}
