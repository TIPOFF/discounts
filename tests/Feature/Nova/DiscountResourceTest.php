<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Feature\Nova;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\TestCase;

class DiscountResourceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function index()
    {
        Discount::factory()->count(4)->create();

        $this->actingAs(self::createPermissionedUser('view discounts', true));

        $response = $this->getJson('nova-api/discounts')
            ->assertOk();

        $this->assertCount(4, $response->json('resources'));
    }
}
