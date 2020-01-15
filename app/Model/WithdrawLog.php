<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Model\WithdrawLog
 *
 * @property int $id
 * @property int $uid 用户id
 * @property string $assets_type 资产类型
 * @property string|null $address 提现到地址
 * @property float $amount 数量
 * @property float $exp 经验值
 * @property int $status 状态 1默认 2成功
 * @property string|null $tx_hash 哈希
 * @property string $ip 操作IP
 * @property string|null $user_agent 浏览器信息
 * @property string|null $msg 转账错误信息
 * @property string|null $port_number
 * @property string|null $net_type
 * @property int|null $code 转账错误码
 * @property int|null $hour 提现时刻唯一标识
 */
class WithdrawLog extends Model{

    public $table = "withdraw_log";

    const STATUS_DEFAULT = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_CHECK = 3;

    /**
     * 订单状态
     * @var array
     */
    public static $statusLabel = array(
        1 => "默认",
        2 => "成功",
        3 => "审核中",
        4 => "拒绝",
    );
}
