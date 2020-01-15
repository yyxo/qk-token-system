<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Users extends Model{

    protected $table = 'users';
    public $prefix = 'token';

    //父子关系最大限制代数
    public static $P_TREE_LAYER_COUNT = 32;

    /**
     * 生产密码
     * @param $prefix
     * @param $username
     * @param $password
     * @param $salt
     * @return string
     */
    public static function hashPassword($prefix,$username,$password,$salt)
    {
        return hash('sha256', $prefix . $username . $password . $salt, false);
    }


    /**
     * 创建昵称
     */
    public static function createNickname($lang = 'zh'){
        while(true){
            $nickname = trans('international.user', [], $lang) . '_' . Str::random(8);

            if(self::where('nickname', $nickname)->count() == 0){
                return $nickname;
            }
        }
    }

    /**
     * 创建邀请码
     */
    public static function createCodeInvite(){
        while(true){
            $code_invite = Str::random(6);

            if(self::where('code_invite', $code_invite)->count() == 0){
                return $code_invite;
            }
        }
    }
}
