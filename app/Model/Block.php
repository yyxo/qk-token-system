<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Model\Block
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $height
 * @property string $hash
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Model\Block whereUpdatedAt($value)
 */
class Block extends Model
{
    protected $table = "block";
}
