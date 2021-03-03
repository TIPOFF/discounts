<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\Discounts\Models\Discount;

class DiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if ($this->faker->boolean) {
            $amount = $this->faker->numberBetween(100, 1000);
            $percent = null;
        } else {
            $amount = null;
            $percent = $this->faker->numberBetween(1, 50);
        }

        return [
            'name'          => $this->faker->unique()->word,
            'code'          => $this->faker->md5,
            'amount'        => $amount,
            'percent'       => $percent,
            'applies_to'    => $this->faker->randomElement(AppliesTo::getEnumerators()),
            'max_usage'     => $this->faker->randomElement([1, 1, 1, 1, 5, 100, 1000]),
            'auto_apply'    => $this->faker->boolean,
            'expires_at'    => $this->faker->dateTimeBetween($startDate = '-1 months', $endDate = '+3 years', $timezone = null),
            'creator_id'    => randomOrCreate(app('user')),
            'updater_id'    => randomOrCreate(app('user')),
        ];
    }

    public function percent(?int $percent = null): self
    {
        return $this->state(function (array $attributes) use ($percent) {
            return [
                'amount'  => null,
                'percent' => $percent ?: $this->faker->numberBetween(1, 50),
            ];
        });
    }

    public function amount(?int $amount = null): self
    {
        return $this->state(function (array $attributes) use ($amount) {
            return [
                'amount'  => $amount ?: $this->faker->numberBetween(100, 1000),
                'percent' => null,
            ];
        });
    }

    public function expired(bool $isExpired = true): self
    {
        return $this->state(function (array $attributes) use ($isExpired) {
            return [
                'expires_at' => $isExpired
                    ? $this->faker->dateTimeBetween($startDate = '-2 months', $endDate = '-1 month', $timezone = null)
                    : $this->faker->dateTimeBetween($startDate = '1 month', $endDate = '2 months', $timezone = null),
            ];
        });
    }

    public function autoApply(bool $isAutoApply = true): self
    {
        return $this->state(function (array $attributes) use ($isAutoApply) {
            return [
                'auto_apply' => $isAutoApply,
            ];
        });
    }
}
