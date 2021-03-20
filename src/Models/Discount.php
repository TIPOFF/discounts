<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Discounts\Exceptions\UnsupportedDiscountTypeException;
use Tipoff\Discounts\Services\Discount\CalculateAdjustments;
use Tipoff\Discounts\Transformers\DiscountTransformer;
use Tipoff\Support\Casts\Enum;
use Tipoff\Support\Contracts\Checkout\CartInterface;
use Tipoff\Support\Contracts\Checkout\Discounts\DiscountInterface;
use Tipoff\Support\Contracts\Checkout\OrderInterface;
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

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'code' => 'string',
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
            $discount->max_usage = $discount->max_usage ?: 1;

            Assert::lazy()
                ->that(strlen($discount->code), 'code')->notEq(9)
                ->that(empty($discount->amount) && empty($discount->percent), 'amount')->false('A discount must have either an amount or percent.')
                ->that(! empty($discount->amount) && ! empty($discount->percent), 'amount')->false('A discount cannot have both an amount & percent.')
                ->verifyNow();
        });
    }

    //region RELATIONSHIPS

    public function carts()
    {
        return $this->belongsToMany(app('cart'))->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(app('order'));
    }

    //endregion

    //region SCOPES

    /**
     * Scope discounts to valid ones.
     *
     * @param Builder $query
     * @param string|Carbon $date
     * @return Builder
     */
    public function scopeValidAt(Builder $query, $date): Builder
    {
        return $query->where(function (Builder $q) use ($date) {
            $q->whereDate('expires_at', '>=', $date);
            $q->orWhereNull('expires_at');
        });
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $this->scopeValidAt($query, new Carbon('now'));
    }

    public function scopeIsActiveAutoApply(Builder $query): Builder
    {
        return $this->scopeAvailable($query)->where('auto_apply', '=', true);
    }

    public function scopeByCartId(Builder $query, int $cartId, bool $autoApply = false): Builder
    {
        return $query->where(function (Builder $query) use ($cartId, $autoApply) {
            $query->whereHas('carts', function ($q) use ($cartId) {
                $q->where('id', $cartId);
            });
            if ($autoApply) {
                $query->orWhere(function (Builder $query) {
                    $query->isActiveAutoApply();
                });
            }
        });
    }

    public function scopeByOrderId(Builder $query, int $orderId): Builder
    {
        return $query->whereHas('orders', function ($q) use ($orderId) {
            $q->where('id', $orderId);
        });
    }

    //endregion

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

    //region INTERFACE

    public static function findByCode(string $code)
    {
        return Discount::query()->available()->where('code', $code)->first();
    }

    public static function calculateAdjustments(CartInterface $cart): void
    {
        app(CalculateAdjustments::class)($cart);
    }

    public static function getCodesForCart(CartInterface $cart): array
    {
        return Discount::query()->byCartId($cart->getId())->get()->all();
    }

    public static function getCodesForOrder(OrderInterface $order): array
    {
        return Discount::query()->byOrderId($order->getId())->get()->all();
    }

    public function getTransformer($context = null)
    {
        return new DiscountTransformer();
    }

    public function getViewComponent($context = null)
    {
        return implode('-', ['tipoff', 'discount', $context]);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function applyToCart(CartInterface $cart)
    {
        // Check for supported discount type
        if (in_array($this->applies_to->getValue(), array_keys(config('discounts.applications')))) {
            $this->carts()->syncWithoutDetaching([$cart->getId()]);

            return $this;
        }

        throw new UnsupportedDiscountTypeException($this->applies_to);
    }

    public function removeFromCart(CartInterface $cart)
    {
        $this->carts()->detach($this->id);

        return $this;
    }

    //endregion

    //region PROTECTED HELPERS

    //endregion
}
