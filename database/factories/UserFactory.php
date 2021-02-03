<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Discounts\Tests\Support\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
        ];
    }
}
