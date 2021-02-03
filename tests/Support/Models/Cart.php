<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tipoff\Discounts\Models\Discount;

class Cart extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function discounts()
    {
        return $this->belongsToMany(Discount::class)->withTimestamps();
    }
}
