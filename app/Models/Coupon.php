<?php

namespace App\Models;

use App\Constant\CouponStatusType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Sluggable;

    /**
     * 批量賦值 - 白名單
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'sub_title',
        'image',
        'content',
        'tag',
        'old_price',
        'new_price',
        'discount',
        'start_at',
        'end_at',
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
        'old_price' => 'integer',
        'new_price' => 'integer',
        'discount'  => 'float',
        'start_at'  => 'datetime:Y-m-d',
        'end_at'    => 'datetime:Y-m-d',
        'status'    => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
    ];

    /**
     * 將 id 改用 slug.
     *
     * @var array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'slug'
            ]
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function collects()
    {
        return $this->hasMany('App\Models\Collect');
    }

    public function getStartAtAttribute()
    {
        if (!$this->attributes['start_at']) {
            return;
        }

        return Carbon::createFromFormat('Y-m-d', $this->attributes['start_at'])->toDateString();
    }

    public function getEndAtAttribute()
    {
        if (!$this->attributes['end_at']) {
            return;
        }

        return Carbon::createFromFormat('Y-m-d', $this->attributes['end_at'])->toDateString();
    }

    public function getStartAtTimestampAttribute()
    {
        return $this->attributes['start_at']
            ? strtotime($this->attributes['start_at'])
            : strtotime($this->attributes['created_at']);
    }

    public function getDiscountPercentAttribute()
    {
        return $this->attributes['discount'] * 100 . "折";
    }

    // 是否為期限內
    public function getIsValidityAttribute()
    {
        if (!$this->attributes['end_at']) {
            return false;
        }

        if ($this->attributes['end_at'] < date('Y-m-d')) {
            return false;
        }

        return true;
    }

    public function getStatusNameAttribute()
    {
        if (!$this->attributes['end_at']) {
            return;
        }

        if ($this->attributes['status'] === CouponStatusType::USABLE) {
            if ($this->attributes['end_at'] < date('Y-m-d')) {
                return '+ (可以試試)';
            }
        }

        if ($this->attributes['status'] === CouponStatusType::EXPIRED) {
            if ($this->attributes['end_at'] < date('Y-m-d')) {
                return '(已失效)';
            }
        }

        return '止';
    }

    public function getHotLevelAttribute()
    {
        if (($this->attributes['discount'] * 100) <= 54) {
            return 'lv3';
        }

        if (($this->attributes['discount'] * 100) <= 60) {
            return 'lv2';
        }

        return;
    }

    public function getNewPriceNameAttribute()
    {
        return $this->attributes['new_price'] . "元";
    }

    public function getEndAtNameAttribute()
    {
        return $this->attributes['end_at']
            ? sprintf('期限 %s', $this->attributes['end_at'])
            : '無限期';
    }

    public function getHotCouAttribute()
    {
        return round($this->attributes['view_cou']/10) + $this->attributes['collect_cou'] + $this->attributes['comment_cou'];
    }

    public function getImageNameAttribute()
    {
        return sprintf('/img/%s.jpg', $this->attributes['image']);
    }
}
