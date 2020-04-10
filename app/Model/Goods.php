<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';
    const TYPE_BUY = 1; //买
    const TYPE_SELL = 2;//卖

    const STATUS_PUBLISHING = 1;//发布中
    const STATUS_DONE = 2;//交易完成
    const STATUS_CANCELED = 4;//已取消

    /**
     * 商品状态
     * @var array
     */
    public static $goodsStatus = array(
        0 => "未审核",
        1 => "发布中",
        2 => "已完成",
        3 => "撤单中",
        4 => "撤单成功",
        5 => "取消广告",
    );
}
