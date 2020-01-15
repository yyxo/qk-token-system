<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Password extends Model{

    protected $table = "password";


    /**
     * 生成加密密码
     * @param $username
     * @param $password
     * @param $salt
     * @return string
     */
    public static function createPassword($username, $password, $salt){
        $prefix = 'prefix_840K#%^#@_';

        return hash('sha256', $prefix . $username . $password . $salt);
    }
}
