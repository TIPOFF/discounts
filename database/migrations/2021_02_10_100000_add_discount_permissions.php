<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddDiscountPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view discounts' => ['Owner', 'Staff'],
            'create discounts' => ['Owner'],
            'update discounts' => ['Owner']
        ];

        $this->createPermissions($permissions);
    }
}
