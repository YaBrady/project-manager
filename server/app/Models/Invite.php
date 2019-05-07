<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $fillable = ['receive_status','status'];

    /**
     * 多态
     */
    public function invitable()
    {
       return $this->morphTo();
    }

    /**
     * 获取该用户所有的邀请
     * @param $query
     * @return
     */
    public function scopeUserInvites($query, $user_id)
    {
        return $query->where([
           [ 'invite_user_id',$user_id],
           [ 'status',0],
           [ 'receive_status',0],
        ]);
    }
}