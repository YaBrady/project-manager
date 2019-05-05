<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //         $this->call(SmsAccountSeeder::class);
        factory('App\Models\Activity', 1)->create()->each(function ($u) {
            factory('App\Models\Rights', 6)->create(
                ['activity_id' => $u->id]
            );
            factory('App\Models\Ad', 3)->create(
                ['activity_id' => $u->id]
            );
        });
    }
}
