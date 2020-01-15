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


    public function __construct(){
        
        $this->middleware(function($request, $next){
            
            $user = session('user');

            if(!empty($user['id'])){
                $this->uid = $user['id'];
            }
            
            return $next($request);
        });
    }
}
