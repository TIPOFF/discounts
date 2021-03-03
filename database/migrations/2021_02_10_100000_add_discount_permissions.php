<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddDiscountPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view discounts',
            'create discounts',
            'update discounts'
        ];

        $this->createPermissions($permissions);
    }
}
