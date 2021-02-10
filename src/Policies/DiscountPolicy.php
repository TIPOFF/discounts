<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Support\Contracts\Models\UserInterface;

class DiscountPolicy
{
    use HandlesAuthorization;

    public function viewAny(UserInterface $user): bool
    {
        return $user->hasPermissionTo('view discounts') ? true : false;
    }

    public function view(UserInterface $user, Discount $discount): bool
    {
        return $user->hasPermissionTo('view discounts') ? true : false;
    }

    public function create(UserInterface $user): bool
    {
        return $user->hasPermissionTo('create discounts') ? true : false;
    }

    public function update(UserInterface $user, Discount $discount): bool
    {
        return $user->hasPermissionTo('update discounts') ? true : false;
    }

    public function delete(UserInterface $user, Discount $discount): bool
    {
        return false;
    }

    public function restore(UserInterface $user, Discount $discount): bool
    {
        return false;
    }

    public function forceDelete(UserInterface $user, Discount $discount): bool
    {
        return false;
    }
}
