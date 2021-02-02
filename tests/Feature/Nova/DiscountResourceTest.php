<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Tests\TestCase;

class DiscountResourceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create_amount_discount()
    {
        $json = $this->getJson("nova-api/discounts");
        dump($json);
    }
}
