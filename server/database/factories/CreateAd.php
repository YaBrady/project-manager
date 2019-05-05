<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Ad::class, function (Faker $faker) {
    return [
        'title'         => '广告的标题',
        'image'         => $faker->imageUrl(),
        'is_link'       => random_int(0, 1),
        'link_url'      => $faker->url,
        'positon'       => random_int(1, 3),
        'list_position' => random_int(0, 4),
        'reply_data'    => '跳转到客户消息返回的内容',
        'reply_title'   => '跳转到客户消息返回的内容（标题）',
        'reply_img'     => $faker->imageUrl(),
        'reply_intro'   => '跳转到客户消息返回的内容（描述）',
        'reply_link'    => $faker->url,
    ];
});
