<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Migrations;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionServiceProvider;
use Tipoff\Discounts\Tests\TestCase;

class PermissionsMigrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            PermissionServiceProvider::class,
        ]);
    }

    /** @test */
    public function permissions_seeded()
    {
        $this->assertTrue(Schema::hasTable('permissions'));
        $this->assertDatabaseCount('permissions', 3);
    }
}
