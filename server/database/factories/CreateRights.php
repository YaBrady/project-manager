<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Rights::class, function (Faker $faker) {
    return [
        'title' => '权益的标题',
        'intro' => '权益的兑奖说明（请于X月X日【携身份证】前往瀚信“浦发信用卡”领取权益）',
        'image' => $faker->imageUrl(),
        'stock' => random_int(30, 50),
        'fee'   => 1,
        //
    ];
});
