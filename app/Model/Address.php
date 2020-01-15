<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * Class FreezeLog
 *
 * @package App\Model
 * @property int $id
 * @property int $uid 用户id
 * @property $address 地址
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $remark 备注
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereOperateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereUserAgent($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereAmountBeforeChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Address whereAddress($value)
 */
class Address extends Model{

    protected $table = "address";
}
