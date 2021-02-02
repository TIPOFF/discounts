<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountOrderTable extends Migration
{
    public function up()
    {
        Schema::create('discount_order', function (Blueprint $table) {
            // TODO - refactor
            $orderModel = config('discounts.model_class.order');
            $orderTable = (new $orderModel)->getTable();

            $table->foreignId('discount_id')->index()->references('id')->on('discounts');
            $table->foreignId('order_id')->index()->references('id')->on($orderTable);
            $table->timestamps();
        });
    }
}
