<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Model\Token
 *
 * @property int $id
 * @property string $assets_id
 * @property string $amount
 * @property string $assets_name
 * @property string $tx_id
 * @property string $operate_type
 * @property string $amount_before_change
 * @property string $tx_hash
 * @property string $ip
 * @property string $user_agent
 * @property string $remark
 * @property string $port_number
 * @property int $uid uid
 */
class BalancesLogs extends Model{

    protected $table = 'balances_logs';
    public static $operate_type_label = [
        'recharge' => '充值',
        'withdraw' => '提现',
    ];
    
    public static $operate_type_label_en = [
        'recharge' => 'Recharge',
        'withdraw' => 'Withdraw',
    ];
}
