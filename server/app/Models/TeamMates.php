<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TeamMates extends Model
{
    public $timestamps = false;
    /**
     * 获取团队
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * 获取用户信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}