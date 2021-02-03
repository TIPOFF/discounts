<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Feature\Nova;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NovaTesting\NovaAssertions;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\Support\Models\User;
use Tipoff\Discounts\Tests\TestCase;

class DiscountResourceTest extends TestCase
{
    use DatabaseTransactions;
    use NovaAssertions;

    /** @test */
    public function index()
    {
        Discount::factory()->count(4)->create();

        $this->be(User::factory()->create());

        $this->novaIndex('discounts')
            ->assertOk()
            ->assertResourceCount(4);
    }
}
