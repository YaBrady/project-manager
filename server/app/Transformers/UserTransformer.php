<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'user' => [
                'email'      => $user->email,
                'name'       => $user->name,
                'avatar'       => $user->avatar,
                'created_at' => $user->created_at ? $user->created_at->toDateString() : null,
            ]
        ];
    }
}