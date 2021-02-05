<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tipoff\Support\Enums\AppliesTo;

class CreateDiscountsTable extends Migration
{
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->index()->unique();

            // Type. Can only be one of the following, amount or percent. Restraints needed to keep the other zero.
            $table->unsignedInteger('amount')->nullable(); // In cents.
            $table->unsignedDecimal('percent', 5, 2)->nullable(); // Allows option to use a discount per booking

            // Application. Definitions include: 'order', 'each' (each one of the products or bookings in orders), 'product', 'booking', 'participant', 'single_participant'
            $table->string('applies_to')->default(AppliesTo::ORDER);

            // Characteristics
            $table->unsignedInteger('max_usage'); // Maximum amount of times the discount can be used in an order
            $table->boolean('auto_apply')->default(false)->index(); // Some discounts (weekday games, multiple bookings in order) will be automatically applied during checkout process.
            $table->date('expires_at')->nullable();

            $table->foreignIdFor(config('discounts.model_class.user'), 'creator_id');
            $table->foreignIdFor(config('discounts.model_class.user'), 'updater_id');
            $table->timestamps();
        });
    }
}
