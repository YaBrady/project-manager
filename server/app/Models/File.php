<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    /**
     * 多态
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function filable()
    {
        return $this->morphTo();
    }

    /**
     * 获取创建人
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}