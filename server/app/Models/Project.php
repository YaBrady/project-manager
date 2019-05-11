<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;
    /**
     * 获取项目成员
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectMate()
    {
        return $this->hasMany(ProjectMate::class);
    }
    /**
     * 获取项目邀请
     */
    public function invites()
    {
        return $this->morphMany(Invite::class,'invitable');
    }

    /**
     * 获取锁关联的团队
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeams()
    {
        return $this->projectMate()->distinct()->select('team_id')->get();
    }

    /**
     * 获取所有条目
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(ProjectItem::class);
    }
}
