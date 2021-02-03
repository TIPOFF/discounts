<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tipoff\Discounts\Contracts\DiscountableCart;

class Cart extends Model implements DiscountableCart
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getTotalParticipants(): int
    {
        return 4;
    }
}
