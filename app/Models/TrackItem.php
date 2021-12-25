<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackItem extends Model
{
    use HasFactory;

    /**
     * 批量賦值 - 白名單
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'track_id',
        'type',
        'title',
        'url',
        'status',
        'created_at',
    ];

    /**
     * 隱藏欄位
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
        'cls',
        'type_name',
    ];

    /**
     * 未使用時間戳
     */
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function track()
    {
        return $this->belongsTo('App\Models\Track');
    }

    public function getUrlAttribute()
    {
        return 'https://www.ptt.cc' . $this->attributes['url'];
    }

    public function getClsAttribute()
    {
        $item = explode('/', $this->attributes['url']);
        return $item[2];
    }

    public function getTypeNameAttribute()
    {
        if ($this->attributes['type'] == 'push') {
            return '推文數';
        } else if ($this->attributes['type'] == 'keyword') {
            return '關鍵字';
        }
    }
}
