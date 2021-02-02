<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartDiscountPivotTable extends Migration
{
    public function up()
    {
        Schema::create('cart_discount', function (Blueprint $table) {
            $table->integer('cart_id')->unsigned()->index();
            $table->integer('discount_id')->unsigned()->index();
            $table->timestamps();
        });
    }
}
