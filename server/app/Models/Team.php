<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * 后去团队邀请人
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teamMate()
    {
        return $this->hasMany(TeamMates::class);
    }

    /**
     * 获取团队邀请
     */
    public function invites()
    {
        return $this->morphMany(Invite::class,'invitable');
    }
}