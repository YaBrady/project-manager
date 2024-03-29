<?php

namespace App\Models;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * 当前用户数据模型
 *
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'deleted_at'
    ];

    /**
     * 返回了 User 的 id
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * 额外在 JWT 载荷中增加的自定义内容
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * 获取我所创建的团队
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    /**
     * 获取我所关联的团队
     */
    public function teamMate()
    {
        return $this->hasMany(TeamMates::class);
    }
    /**
     * 获取我所关联项目
     */
    public function projectMate()
    {
        return $this->hasMany(ProjectMate::class);
    }

    /**
     * 查询是否有该团队
     *
     * @param $team_id
     * @throws Exception
     */
    public function hasThisTeam($team_id)
    {
        if(!$this->teams()->find($team_id)) {
            throw new Exception('您无权限');
        }
    }

    /**
     * 查询是否有该项目
     *
     * @param $project_id
     * @throws Exception
     */
    public function hasThisProject($project_id)
    {
        if(!$this->projects()->where('id' ,$project_id)->exists()) {
            throw new Exception('您无权限');
        }
    }

    public function isJoinProject($project_id)
    {
        if(!$this->projectMate()->where('project_id' ,$project_id)->exists()) {
            throw new Exception('您无权限');
        }
    }


}
