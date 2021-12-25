<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 批量賦值 - 白名單
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'coupon_id',
        'tag',
        'content',
        'ip',
        'created_at',
    ];

    /**
     * 隱藏欄位
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'user_id',
        'coupon_id',
        'tag',
        'ip',
        'deleted_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:m/d',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'tag_name',
        'tag_css',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function coupon()
    {
        return $this->belongsTo('App\Models\Coupon');
    }

    public function getTagNameAttribute()
    {
        if ($this->attributes['tag'] == 'use_true'){
            return "可以使用";
        }

        if ($this->attributes['tag'] == 'use_false') {
            return "不可使用";
        }
        return "＠　　　";
    }

    public function getTagCssAttribute()
    {
        if ($this->attributes['tag'] == 'use_true') {
            return "btn-outline-success";
        }

        if ($this->attributes['tag'] == 'use_false') {
            return "btn-outline-danger";
        }
        return "btn-outline-info";
    }

    public function getNameAttribute()
    {
        if ($this->attributes['user_id'] == 2) {
            $temp = Str::of($this->attributes['content'])->explode(': ');
            return isset($temp[1]) ? $temp[0] . '.' : $this->attributes['name'];
        }
        return $this->attributes['name'];
    }

    public function getContentAttribute()
    {
        if ($this->attributes['user_id'] == 2) {
            $temp = Str::of($this->attributes['content'])->explode(': ');
            return $temp[1] ?? $this->attributes['content'];
        }
        return $this->attributes['content'];
    }

}
