<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Model\Settings
 *
 * @property int $id
 * @property string $key
 * @property string $value
 */
class Settings extends Model{

    public $table = 'settings';


    /**
     * 根据key获取value
     *
     * @param $key
     * @return null
     */
    public static function getValueByKey($key){
        return self::where("key", $key)->first()->value ?? null;
    }
}
