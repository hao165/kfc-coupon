<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    /**
     * 批量賦值 - 白名單
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'cls',
        'page',
        'keyword',
        'push',
        'status',
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
    protected $casts = [
        'keyword' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function items()
    {
        return $this->hasMany('App\Models\TrackItem');
    }

    public function getUrlAttribute()
    {
        return 'https://www.ptt.cc/bbs/' . $this->attributes['cls'] . '/index.html';
    }

}
