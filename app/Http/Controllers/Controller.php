<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


class Controller extends BaseController{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $user; //用户信息

    public function __construct(){
        
        $this->middleware(function($request, $next){
            
            $user = session('user');

            $this->user = $user;

            if(!empty($user['id'])){
                $this->uid = $user['id'];
            }
            
            return $next($request);
        });
    }
}
