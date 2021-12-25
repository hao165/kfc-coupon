<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crawler extends Model
{
    use HasFactory;

    /**
     * 批量賦值 - 白名單
     *
     * @var string[]
     */
    protected $fillable = [
        'cls',
        'slug',
        'title',
        'last_at',
        'status',
    ];

    /**
     * 隱藏欄位
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
    ];

    public function items()
    {
        return $this->hasMany('App\Models\CrawlerItem');
    }

    public function getUrlAttribute()
    {
        return 'https://www.ptt.cc/bbs/' . $this->attributes['cls'] . '/' . $this->attributes['slug'] . '.html';
    }

}
