<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Model\Assets;
use App\Model\Boxes;
use App\Model\Token;
use App\Model\User;
use App\Service\BalanceService;
use App\Service\BalancesService;
use App\Service\BoxesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BoxController extends Controller
{
    /**
     * 首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function Index()
    {
        return view('Box.index',[]);
    }

    public function userinfo()
    {
        //我的余额QKF
        $data['cct'] = number_format(BalancesService::getBalanceByName($this->uid, 'cct')) ?? null;
        $data['qki'] = number_format(BalancesService::getBalanceByName($this->uid, 'qki')) ?? null;
        return response()->json(['code' => 0, 'msg' => "获取成功",'data'=>$data]);
    }


    public function boxes()
    {
        $data['boxes'] = Boxes::where('uid',$this->uid)
            ->orderByDesc('id')
            ->get();

        $tokens = Assets::get();

        $token_name = [];

        foreach ($tokens as $token) {
            $token_name[$token->id] = $token->assets_name;
        }

        foreach ($data['boxes'] as &$box) {
            $box['color_name'] = Boxes::$color_name[$box->color];
            $box['token_name'] = $token_name[$box->assets_id]??'token';
            $box['qkf_amount'] = bcmul($box->amount,'1.8',1);
        }
        return response()->json(['code' => 0, 'msg' => "获取成功",'data'=>$data]);
    }


    public function open(Request $request)
    {
        $amount = (int)$request->input("amount");
        $color = (int)$request->input("color");
        $token = trim($request->input("token"));
        if(!in_array($token,['cct','qki']))
        {
            return response()->json(['code' => 202, 'msg' => "类型错误"]);

        }
        $token_type = Assets::where('assets_name',$token)->first();

        if($amount <= 0)
            return response()->json(['code' => 201, 'msg' => "数量错误"]);
        $box = new BoxesService();
        if($box->open($this->uid,$amount,$color,$token_type))
            return response()->json(['code' => 0, 'msg' => "获取箱子成功，开启中"]);
        else
            return response()->json(['code' => 203, 'msg' => "获取箱子失败"]);

    }
}
