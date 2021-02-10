<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Models;

use Assert\LazyAssertionException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\Support\Models\Cart;
use Tipoff\Discounts\Tests\TestCase;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\TestSupport\Models\User;

class DiscountPolicyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function view_any()
    {
        $user = self::getUser('view discounts', true);
        $this->assertTrue($user->can('viewAny', Discount::class));

        $user = self::getUser('view discounts', false);
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
            'view-true' => [ 'view', self::getUser('view discounts', true), true ],
            'view-false' => [ 'view', self::getUser('view discounts', false), false ],
            'create-true' => [ 'create', self::getUser('create discounts', true), true ],
            'create-false' => [ 'create', self::getUser('create discounts', false), false ],
            'update-true' => [ 'update', self::getUser('update discounts', true), true ],
            'update-false' => [ 'update', self::getUser('update discounts', false), false ],
            'delete-true' => [ 'delete', self::getUser('delete discounts', true), false ],
            'delete-false' => [ 'delete', self::getUser('delete discounts', false), false ],
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

    static private function getUser(string $permission, bool $hasPermission): UserInterface
    {
        /**
         * Normally, this would be done with a makePartial() mock, but the mock gets lost
         * and the real user class is used when the permission method is invoked.  So, we
         * establish expectations in directly in an authenticatable class instance.
         */
        $user = new class extends User {
            private string $permission;
            private bool $hasPermission;

            public function hasPermissionTo($permission, $guardName = null): bool
            {
                return $this->permission === $permission ? $this->hasPermission : false;
            }

            public function setHasPermission(string $permission, bool $hasPermission): self
            {
                $this->permission = $permission;
                $this->hasPermission = $hasPermission;

                return $this;
            }
        };

        return $user->setHasPermission($permission, $hasPermission);
    }
}
