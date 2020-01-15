<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * 新的余额表
 * Class Balances
 *
 * @package App\Model
 * @property int $id
 * @property int $uid 用户id
 * @property string $name 资产名称
 * @property int|null $token_id 通证id
 * @property int $assets_id 资产类型
 * @property float $amount 可用金额
 * @property float $freeze_amount 冻结金额
 */
class Balances extends Model{

    protected $table = 'balances';
}
