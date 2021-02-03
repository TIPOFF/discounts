<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Contracts;

interface DiscountableCart
{
    public function getId(): int;

    public function getTotalParticipants(): int;
}
