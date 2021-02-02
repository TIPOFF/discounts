<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\Models\Cart;
use Tipoff\Discounts\Tests\Models\Order;
use Tipoff\Discounts\Tests\Models\User;
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
