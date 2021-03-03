<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\View\Components;

use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\TestCase;

class DiscountComponentTest extends TestCase
{
    /** @test */
    public function single_adjustment()
    {
        $discount = Discount::factory()->amount(1234)->create();

        $view = $this->blade(
            '<x-tipoff-cart-deductions :deductions="$deductions" />',
            ['deductions' => [
                $discount,
            ]]
        );

        $view->assertSee($discount->code);
        $view->assertSee('Discount: $12.34');
    }

    /** @test */
    public function multiple_adjustments()
    {
        $discount1 = Discount::factory()->amount(123)->create();
        $discount2 = Discount::factory()->amount(234)->create();

        $view = $this->blade(
            '<x-tipoff-cart-deductions :deductions="$deductions" />',
            ['deductions' => [
                $discount1,
                $discount2,
            ]]
        );

        $view->assertSee($discount1->code);
        $view->assertSee('Discount: $1.23');
        $view->assertSee($discount2->code);
        $view->assertSee('Discount: $2.34');
    }
}
