<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Models;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\TestCase;
use Tipoff\Support\Contracts\Models\UserInterface;

class DiscountPolicyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function view_any()
    {
        $user = self::createPermissionedUser('view discounts', true);
        $this->assertTrue($user->can('viewAny', Discount::class));

        $user = self::createPermissionedUser('view discounts', false);
        $this->assertFalse($user->can('viewAny', Discount::class));
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_as_creator
     */
    public function all_permissions_as_creator(string $permission, UserInterface $user, bool $expected)
    {
        $discount = Discount::factory()->make([
            'creator_id' => $user,
        ]);

        $this->assertEquals($expected, $user->can($permission, $discount));
    }

    public function data_provider_for_all_permissions_as_creator()
    {
        return [
            'view-true' => [ 'view', self::createPermissionedUser('view discounts', true), true ],
            'view-false' => [ 'view', self::createPermissionedUser('view discounts', false), false ],
            'create-true' => [ 'create', self::createPermissionedUser('create discounts', true), true ],
            'create-false' => [ 'create', self::createPermissionedUser('create discounts', false), false ],
            'update-true' => [ 'update', self::createPermissionedUser('update discounts', true), true ],
            'update-false' => [ 'update', self::createPermissionedUser('update discounts', false), false ],
            'delete-true' => [ 'delete', self::createPermissionedUser('delete discounts', true), false ],
            'delete-false' => [ 'delete', self::createPermissionedUser('delete discounts', false), false ],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_not_creator
     */
    public function all_permissions_not_creator(string $permission, UserInterface $user, bool $expected)
    {
        $discount = Discount::factory()->make();

        $this->assertEquals($expected, $user->can($permission, $discount));
    }

    public function data_provider_for_all_permissions_not_creator()
    {
        // Permissions are identical for creator or others
        return $this->data_provider_for_all_permissions_as_creator();
    }
}
