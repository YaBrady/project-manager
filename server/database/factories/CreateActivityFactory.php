<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Activity::class, function (Faker $faker) {
    return [
        'start_time'           => $faker->dateTimeBetween('+1 months', '+2 months'),
        'end_time'             => $faker->dateTimeBetween('+3 months', '+4 months'),
        'title'                => '活动名称活动名称',
        'intro'                => '活动说明富文本活动说明富文本活动说明富文本',
        'qrcode'               => $faker->url,
        'share_title'          => '小程序分享的标题小程序分享的标题',
        'share_img'            => $faker->imageUrl(),
        'not_enough_stock_txt' => '库存不足的弹窗文案库存不足的弹窗文案',
        'user_get_count'       => random_int(1, 2)
    ];
});
