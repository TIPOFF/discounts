<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Support\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tipoff\Support\Models\TestModelStub;

class User extends Authenticatable
{
    use TestModelStub;

    protected $guarded = ['id'];
}
