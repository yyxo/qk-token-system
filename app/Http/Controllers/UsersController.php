<?php

namespace App\Http\Controllers;

use App\Model\Users;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        $data['title'] = "个人中心";
        /**
         * @var User $user
         */
        $user = session('user');
        $user = Users::find($user->id);

        $data['user'] = array(
            'username' => $user->username,
            'id' => $user->id,
        );
        return view("User.index", $data);
    }

    /**
     * 退出登录
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout(Request $request)
    {
        $request->session()->forget('user');
        return redirect('login');
    }
}
