<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Seeders;

use Assert\LazyAssertionException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tipoff\Discounts\Database\Seeders\PermissionsSeeder;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\Support\Models\Cart;
use Tipoff\Discounts\Tests\TestCase;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\TestSupport\Models\User;

class PermissionsSeederTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function seed_with_no_table()
    {
        (new PermissionsSeeder())->run();

        $this->assertFalse(Schema::hasTable('permissions'));
    }

    /** @test */
    public function seed_with_table()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name');
            $table->timestamps();
        });

        $this->assertTrue(Schema::hasTable('permissions'));

        (new PermissionsSeeder())->run();

        $this->assertDatabaseCount('permissions', 3);
    }

    /** @test */
    public function seed_with_duplicates()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name');
            $table->timestamps();
        });

        (new PermissionsSeeder())->run();
        (new PermissionsSeeder())->run();

        $this->assertDatabaseCount('permissions', 3);
    }
}
