<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectItem extends Model
{
    use  SoftDeletes;


    /**
     * 获取步骤
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lists()
    {
        return $this->hasMany(ProjectItemList::class);
    }

    /**
     * 获取评论列表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(comment::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'filable');
    }
}