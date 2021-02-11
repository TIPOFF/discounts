<?php

namespace Database\Seeders\Production;

use App\Services\CheckoutService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('discounts')->delete();

        DB::table('discounts')->insert([
            0 =>
            [
                'amount' => 200,
                'applies_to' => CheckoutService::APPLICATION_PARTICIPANT,
                'auto_apply' => 0,
                'code' => 'MILITARY',
                'created_at' => '2020-10-01 22:30:13',
                'creator_id' => 1,
                'expires_at' => NULL,
                'id' => 1,
                'max_usage' => 100000,
                'name' => 'Military',
                'percent' => NULL,
                'updated_at' => '2020-10-01 22:30:13',
                'updater_id' => 1,
            ],
        ]);
    }
}
