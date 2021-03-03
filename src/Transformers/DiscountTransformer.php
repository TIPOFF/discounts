<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Transformers;

use Tipoff\Discounts\Models\Discount;
use Tipoff\Support\Transformers\BaseTransformer;

class DiscountTransformer extends BaseTransformer
{
    protected $defaultIncludes = [
    ];

    protected $availableIncludes = [
    ];

    public function transform(Discount $discount)
    {
        return [
            'id' => $discount->id,
            'name' => $discount->name,
            'code' => $discount->code,
            'amount' => $discount->amount,
            'percent' => $discount->percent,
            'max_usage' => $discount->max_usage,
            'auto_apply' => $discount->auto_apply,
            'applies_to' => $discount->applies_to->getName(),
            'expires_at' => $discount->expires_at,
        ];
    }
}
