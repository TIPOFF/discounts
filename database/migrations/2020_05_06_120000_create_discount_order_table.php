<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tipoff\Discounts\Models\Discount;

class CreateDiscountOrderTable extends Migration
{
    public function up()
    {
        Schema::create('discount_order', function (Blueprint $table) {
            $table->foreignIdFor(app('discount'));
            $table->foreignIdFor(app('order'));
            $table->timestamps();
        });
    }
}
