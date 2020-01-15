<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Model\Transactions
 *
 * @property int $id
 * @property string|null $from 转出地址
 * @property string|null $to 转入地址
 * @property string|null $hash 转账hash
 * @property string|null $block_hash 区块hash
 * @property int $block_number 区块高度
 * @property float $gas_price 矿工费
 * @property float $amount 数量
 * @property int $status 状态，1默认，2已处理
 * @property int $tx_status 交易状态，1成功，0失败
 * @property string $assets_type 资产类型 如果是token，说明是通证，需要通证类型id
 * @property int|null $token_id 通证类型id
 * @property int|null $data_id 处理对应的数据id，充值为assets_logs数据id、提现为withdraw_id、退回为refund_id
 * @property string|null $remark 备注
 * @property int|null $admin_id 如果是管理员操作，则填写此字段
 * @property string|null $payee 合约地址(通证)
 * @property float|null $token_tx_amount 通证交易数量
 * @property int $uid 用户id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Transactions extends Model{

    public $table = 'transactions';
}
