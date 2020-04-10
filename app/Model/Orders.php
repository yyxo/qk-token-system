<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';
    const STATUS_UNPAID = 2;//待付款
    const STATUS_PAYED = 3;//已付款（买家已点击“我已付款”）
    const STATUS_DONE = 5;//交易完成
    const STATUS_CANCELED = 6;//取消订单
    /**
     * 订单状态
     * @var array
     */
    public static $statusLabel = [
        0 => "未托管资产",
        1 => "已转出数字资产",
        2 => "待付款",
        3 => "已付款",
        4 => "已确认收款",
        5 => "交易完成",
        6 => "已取消",
        7 => "后台强制撤回",
    ];
}
