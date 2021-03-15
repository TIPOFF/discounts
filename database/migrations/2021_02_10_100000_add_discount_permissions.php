<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddDiscountPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view discounts' => ['Owner', 'Executive', 'Staff'],
            'create discounts' => ['Owner', 'Executive'],
            'update discounts' => ['Owner', 'Executive']
        ];

        $this->createPermissions($permissions);
    }
}
