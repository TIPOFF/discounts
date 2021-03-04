<?php

declare(strict_types=1);

namespace Tipoff\Discounts\View\Components;

use Illuminate\View\View;
use Tipoff\Checkout\View\Components\BaseDeductionComponent;
use Tipoff\Discounts\Models\Discount;

class DiscountComponent extends BaseDeductionComponent
{
    public Discount $discount;

    public function __construct(Discount $deduction)
    {
        parent::__construct($deduction);
        $this->discount = $deduction;
    }

    public function render()
    {
        /** @var View $view */
        $view = view('discounts::components.discount');

        return $view;
    }
}
