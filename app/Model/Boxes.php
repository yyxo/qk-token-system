<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\Boxes
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $uid
 * @property int $assets_id 合成的目标资产类型
 * @property int $amount 门票数量
 * @property int $height 区块高度
 * @property int $color 1红色 2蓝色
 * @property int $status 0默认 1成功 2失败
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereAssetsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Boxes whereUpdatedAt($value)
 */
class Boxes extends Model
{
    protected $table = 'boxes';

    public static $color_name = ['1'=>"红色",'2'=>'蓝色'];
}
