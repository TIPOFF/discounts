<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Feature\Nova;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Authorization\Models\User;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\TestCase;

class DiscountResourceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @dataProvider dataProviderForShowByRole
     * @test
     */
    public function index_by_role(?string $role, bool $hasAccess)
    {
        Discount::factory()->count(4)->create();

        $user = User::factory()->create();
        if ($role) {
            $user->assignRole($role);
        }
        $this->actingAs($user);

        $response = $this->getJson('nova-api/discounts')
            ->assertStatus($hasAccess ? 200 : 403);

        if ($hasAccess) {
            $this->assertCount(4, $response->json('resources'));
        }
    }

    /**
     * @dataProvider dataProviderForShowByRole
     * @test
     */
    public function show_by_role(?string $role, bool $hasAccess)
    {
        $discount = Discount::factory()->create();

        $user = User::factory()->create();
        if ($role) {
            $user->assignRole($role);
        }
        $this->actingAs($user);

        $this->getJson("nova-api/discounts/{$discount->id}")
            ->assertStatus($hasAccess ? 200 : 403);
    }

    public function dataProviderForShowByRole()
    {
        return [
            'Admin' => ['Admin', true],
            'Owner' => ['Owner', true],
            'Executive' => ['Executive', true],
            'Staff' => ['Staff', true],
            'Former Staff' => ['Former Staff', false],
            'Customer' => ['Customer', false],
            'Participant' => ['Participant', false],
            'No Role' => [null, false],
        ];
    }

    /**
     * @dataProvider dataProviderForDeleteByRole
     * @test
     */
    public function delete_by_role(?string $role, bool $hasAccess, bool $canDelete)
    {
        $discount = Discount::factory()->create();

        $user = User::factory()->create();
        if ($role) {
            $user->assignRole($role);
        }
        $this->actingAs($user);

        // Request never fails
        $this->deleteJson("nova-api/discounts?resources[]={$discount->id}")
            ->assertStatus($hasAccess ? 200 : 403);

        // But deletion will only occur if user has permissions
        $this->assertDatabaseCount('discounts', $canDelete ? 0 : 1);
    }

    public function dataProviderForDeleteByRole()
    {
        return [
            'Admin' => ['Admin', true, false],
            'Owner' => ['Owner', true, false],
            'Executive' => ['Executive', true, false],
            'Staff' => ['Staff', true, false],
            'Former Staff' => ['Former Staff', false, false],
            'Customer' => ['Customer', false, false],
            'Participant' => ['Participant', false, false],
            'No Role' => [null, false, false],
        ];
    }
}
