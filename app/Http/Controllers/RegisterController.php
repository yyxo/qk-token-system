<?php

namespace App\Http\Controllers;

use App\Service\UserService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function login()
    {
        $data['title'] = "登录";
        return view("User.login", $data);
    }
    public function register()
    {
        $data['title'] = "注册";
        return view("User.register", $data);
    }

    public function registerSubmit(Request $request)
    {
        $username = trim($request->input('username'));
        $password = trim($request->input('password'));
        $password2 = trim($request->input('password2'));
        $encrypt_password = trim($request->input('encrypt_password'));
        $invite_uid = trim($request->input('invite_uid'));

        if ($password != $password2) {
            return response()->json(['code' => 109, 'msg' => "两次密码不一致"]);
        }

        if (strlen($password) < 6 || strlen($password) > 20) {
            return response()->json(['code' => 51, 'msg' => "密码长度为6到20位"]);
        }

        $ip = $request->getClientIp();
        try {
            $userService = new UserService();
            if ($userService->register($username, $password, $encrypt_password, $ip, $invite_uid)) {
                return response()->json(['code' => 0, 'msg' => "注册成功"]);
            } else {
                return response()->json(['code' => 101, 'msg' => "注册失败"]);
            }
        } catch (\exception $exception) {
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }

    public function loginSubmit(Request $request)
    {
        $username = trim($request->input('username'));
        $encrypt_password = trim($request->input('encrypt_password'));

        try {
            $userService = new UserService();
            $user = $userService->login($username,$encrypt_password);
            if ($user) {
                session(['user' => $user]);
                return response()->json(['code' => 0, 'msg' => "登录成功"]);
            } else {
                return response()->json(['code' => 101, 'msg' => "登录失败"]);
            }
        } catch (\exception $exception) {
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }
}
