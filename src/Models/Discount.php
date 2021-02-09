<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Tipoff\Support\Casts\Enum;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasCreator;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Support\Traits\HasUpdater;

/**
 * @property string name
 * @property string code
 * @property int amount
 * @property float percent
 * @property AppliesTo applies_to
 * @property int max_usage
 * @property bool auto_apply
 * @property Carbon expires_at
 * // Raw Relations
 * @property int creator_id
 * @property int updater_id
 */
class Discount extends BaseModel
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
        return $this->belongsToMany(app('cart'))->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(app('order'));
    }
}
