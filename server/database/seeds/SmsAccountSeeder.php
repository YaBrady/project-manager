<?php

use Illuminate\Database\Seeder;

class SmsAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $create_time = date('Y-m-d H:i:s', time());
        $insert = [
            ['key' => 'ihuyi', 'username' => 'cf_dggy', 'password' => 'PnaSck', 'created_at' => $create_time],
            ['key' => 'chuanglan', 'username' => 'qmwl888_jyyx', 'password' => 'jtyh@iuyfdsd3', 'created_at' => $create_time],
        ];

        DB::table('sms_accounts')->insert($insert);
    }
}
