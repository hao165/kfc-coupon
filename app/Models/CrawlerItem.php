<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrawlerItem extends Model
{
    use HasFactory;

    /**
     * 批量賦值 - 白名單
     *
     * @var array
     */
    protected $fillable = [
        'crawler_id',
        'status',
        'ptt_id',
        'content',
        'created_at',
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
    ];

    /**
     * 未使用時間戳
     */
    public $timestamps = false;

    public function crawler()
    {
        return $this->belongsTo('App\Models\Crawler');
    }
}
