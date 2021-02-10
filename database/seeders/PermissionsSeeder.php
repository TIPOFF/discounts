<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        if (Schema::hasTable('permissions')) {
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $permissions = ['view discounts', 'create discounts', 'update discounts'];

            DB::table('permissions')->insertOrIgnore(
                collect($permissions)
                    ->map(function (string $name) use ($now) {
                        return [
                            'guard_name' => 'web',
                            'name' => $name,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })
                    ->toArray()
            );
        }
    }
}
