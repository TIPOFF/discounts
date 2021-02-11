<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionRegistrar;

class AddDiscountPermissions extends Migration
{
    public function up()
    {
        if (app()->has(Permission::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            foreach ([
                         'view discounts',
                         'create discounts',
                         'update discounts'
                     ] as $name) {
                app(Permission::class)::findOrCreate($name, null);
            };
        }
    }
}
