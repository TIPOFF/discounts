<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\TestCase;

class DiscountModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function test_create_discount()
    {
        $discount = Discount::factory()->create();
        $this->assertNotNull($discount);
    }
}
