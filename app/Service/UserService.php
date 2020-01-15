<?php


namespace App\Service;

use App\Model\Password;
use App\Model\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use exception;

class UserService
{

    /**
     * 用户注册
     * @param $username
     * @param $password
     * @param $encrypt_password
     * @param $ip
     * @return bool
     * @throws exception
     */
    public function register($username, $password,$encrypt_password, $ip, $invite_uid = 0)
    {
        if ($username == "" || $password == "") {
            throw new exception("请完整填写信息", 50);
        }

        if (strlen($password) < 6 || strlen($password) > 20) {
            throw new exception("密码长度为6到20位", 51);
        }

        //用户主表用户名是否存在
        $userNum = Users::where('username', $username)->count();

        if ($userNum > 0) {
            throw new exception("用户名已存在", 100);
        }

        $salt = Str::random(6);//盐

        $user = new Users();

        $password = new Password();
        $password->password = Users::HashPassword($user->prefix, $username, $encrypt_password, $salt);

        $user->salt = $salt;
        $user->username = $username;
        $user->reg_ip = $ip;
        $user->invite_uid = 0;

        if (is_numeric($invite_uid) && $invite_uid > 0) {
            // 判断邀请人是否存在
            if (User::find($invite_uid)) {
                $user->invite_uid = $invite_uid;
            }
        }

        DB::beginTransaction();
        try {
            if($password->save() && $user->save())
            {
                DB::commit();
                return true;
            }else{
                DB::rollBack();
                return false;
            }
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 用户登录
     * @param $username
     * @param $encrypt_password
     * @return mixed
     * @throws exception
     */
    public function login($username, $encrypt_password)
    {
        $user = Users::where('username', $username)->first();

        if (empty($user)) {
            throw new exception("用户不存在", 102);
        }

        if ($user->status != 1) {
            throw new exception("用户已禁用", 105);
        }

        $hash_password = new Password();
        $hash_password_str = Users::HashPassword($user->prefix, $user->username, $encrypt_password, $user->salt);

        $encrypt_hash_password = $hash_password->where('password', $hash_password_str)->count();

        if (!$encrypt_hash_password) {
            throw new exception("密码错误", 103);
        }

        // 设置session的过期时间
        session(["user_effective_{$user->id}" => time() + 3600]);

        return $user;
    }
}
