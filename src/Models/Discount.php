<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tipoff\Discounts\Enums\AppliesTo;
use Tipoff\Support\Casts\Enum;
use Tipoff\Support\Models\BaseModel;

class Discount extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'name' => 'string',
        'code' => 'string', // TODO - use custom class to represent DiscountCode?
        'amount' => 'integer',
        'percent' => 'float',
        'applies_to' => Enum::class.':'.AppliesTo::class,
        'max_usage' => 'integer',
        'auto_apply' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Discount $discount) {
            // TODO - refactor into Auditable trait?
            if (auth()->check()) {
                $discount->creator_id = auth()->id();
            }
        });

        static::saving(function (Discount $discount) {
            // TODO - refactor into Auditable trait?
            if (auth()->check()) {
                $discount->updater_id = auth()->id();
            }
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
    public function scopeValidAt($query, $date)
    {
        return $query
            ->whereDate('expires_at', '>=', $date);
    }

    /**
     * Validate is current discount is available at specified date.
     *
     * @param string|Carbon $date
     * @return bool
     */
    public function isValidAt($date)
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
        return $this->belongsToMany(config('discounts.model_class.cart'))->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(config('discounts.model_class.order'));
    }

    public function creator()
    {
        return $this->belongsTo(config('discounts.model_class.user'), 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(config('discounts.model_class.user'), 'updater_id');
    }
}
