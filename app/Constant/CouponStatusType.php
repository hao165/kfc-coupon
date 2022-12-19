<?php

namespace App\Constant;

class CouponStatusType
{
    /**
     * 可使用
     *
     * @var string
     */
    const USABLE = 1;

    /**
     * 已失效
     *
     * @var string
     */
    const EXPIRED = 2;

    /**
     * 等待審核
     *
     * @var string
     */
    const PENDING = 3;

    /**
     * 審核失敗
     *
     * @var string
     */
    const FAIL = 4;

    /**
     * 討論區slug-99999
     *
     * @var string
     */
    const CHAT = 99;
}
