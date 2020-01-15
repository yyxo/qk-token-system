<?php

namespace App\Http\Middleware;

use App\Model\Users;
use Closure;

class UserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = session('user');
        $user = Users::where("id",$user['id'])->first();
        if(empty($user))
        {
            if($request->ajax())
            {
                return response()->json(['code' => 10, 'msg' => "请先登录"]);
            }
            return redirect('/login');
        }

        if($user->status == 2)
        {

            if($request->ajax())
            {

                return response()->json(['code' => 10, 'msg' => "此用户已被禁用"]);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
