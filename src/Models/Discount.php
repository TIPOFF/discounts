<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Tipoff\Discounts\Exceptions\ValidationException;
use Tipoff\Discounts\Rules\DiscountCode;

class Discount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Discount $discount) {
            if (auth()->check()) {
                $discount->creator_id = auth()->id();
            }
        });

        static::saving(function (Discount $discount) {
            $discount->code = strtoupper($discount->code);
            if (auth()->check()) {
                $discount->updater_id = auth()->id();
            }
            if (empty($discount->max_usage)) {
                $discount->max_usage = 1;
            }

            $discount->validate();
        });
    }

    protected function rules(): array
    {
        return [
            'code' => new DiscountCode(),
            'amount' => 'required_without:percent',
            'percent' => 'required_without:amount',
            'max_usage' => 'required|number|min:1'
        ];
    }

    protected function validate(): void
    {
        $v = Validator::make([
            'code' => $this->code,
            'amount' => $this->amount,
            'percent' => $this->percent,
        ], $this->rules());

        if ($v->fails()) {
            throw new ValidationException($v);
        }
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
        return $this->belongsToMany(config('discounts.cart.model'))->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(config('discounts.order.model'));
    }

    public function creator()
    {
        return $this->belongsTo(config('discounts.user.model'), 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(config('discounts.user.model'), 'updater_id');
    }
}
