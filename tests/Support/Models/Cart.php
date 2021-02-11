<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Models;

use Tipoff\Checkout\Contracts\Models\CartInterface;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Models\TestModelStub;

class Cart extends BaseModel implements CartInterface
{
    use TestModelStub;

    protected $guarded = [
        'id',
    ];

    public function getTotalParticipants(): int
    {
        return 4;
    }

    public function applyDeductionCode(string $code): CartInterface
    {
    }
}
