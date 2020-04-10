<?php

namespace App\Http\Controllers;

use App\Model\GoodsType;
use App\Service\GoodsService;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    /**
     * 发布广告
     * @param Request $request
     * @param GoodsService $goodsService
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request,GoodsS $goodsService)
    {
        $user = $this->user;

        $type = (int)trim($request->input('goodsType'));
        $uid = $user->id;
        $tradeType = (int)trim($request->input('tradeType'));
        $totalAmount = trim($request->input('amount'));
        $captcha = trim($request->input('captcha'));
        if(!$captcha)
        {
            return response()->json(['code' => 191, 'msg' => "请输入图形验证码"]);
        }

        if ($request->session()->get("captcha_code") != $captcha) {
            //用户输入验证码错误
            return response()->json(['code' => 302, 'msg' => "验证码错误"]);
        } else {
            //验证码用过一次就移除
            $request->session()->forget('captcha_code');
        }

        $goodsType = GoodsType::where("id", $type)->first();
        //USDT发布广告，不需要任何限制
        if(!$goodsType){
            return response()->json(['code' => 191, 'msg' => "广告类型不存在"]);
        }
        if(!in_array($tradeType,[1,2])){
            return response()->json(['code' => 191, 'msg' => "广告类型错误"]);
        }
        $price = trim($request->input('price'));
        $price = bcadd($price,0,'4');//只取4位
        $totalAmount = bcadd($totalAmount,0,'4');//只取4位

        $minBuy = 1;
        $isAnonymous = (int)trim($request->input('isAnonymous',1));


        if($price <= 0)
        {
            return response()->json(['code' => 191, 'msg' => "价格必须大于0"]);
        }

        if($totalAmount <= 0)
        {
            return response()->json(['code' => 192, 'msg' => "数量必须大于0"]);
        }
        //判断是否暂停发布
        if($goodsType->lock!=1 || $goodsType->is_light!=1)
        {
            return response()->json(['code' => 189, 'msg' => "系统维护，该类型广告已暂停发布"]);
        }
        if($goodsType->required_address==2 && $user->address==''){
            return response()->json(['code' => 191, 'msg' => "请先绑定地址"]);
        }

        //判断用户是否异常
        if ($user->status != 1) {
            return response()->json(['code' => 191, 'msg' => "帐号异常，无法发布广告"]);
        }

        //执行创建广告操作
        try {
            $goodsId = $goodsService->publish($uid, $tradeType, $totalAmount, $price, $goodsType, $minBuy,$isAnonymous);
            if ($goodsId) {
                return response()->json(['code' => 0, 'msg' => "发布成功", 'id' => $goodsId]);
            } else {
                return response()->json(['code' => 107, 'msg' => "操作失败"]);
            }
        } catch (\exception $exception) {

            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }

    }


    /**撤单，如果是卖单且未交易或所有订单已完成则自动返回积分
     * @param $id
     * @param GoodsService $goodsService
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id,GoodsService $goodsService)
    {
        $user = $this->user;
        try {
            if ($goodsService->cancel($user->id, $id)) {
                return response()->json(['code' => 0, 'msg' => "操作成功"]);
            } else {
                return response()->json(['code' => 107, 'msg' => "操作失败"]);
            }
        } catch (\exception $exception) {
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }

    /**
     * 点亮广告（显示）
     * @param id $id
     * @param GoodsService $goodsService
     * @return \Illuminate\Http\JsonResponse
     */
    public function shine($id, GoodsService $goodsService)
    {

        $user = $this->user;
        try {
            $goods = $goodsService->shine($id, $user->id);
            if ($goods) {
                return response()->json(['code' => 0, 'msg' => "点亮成功"]);
            } else {
                return response()->json(['code' => 107, 'msg' => "操作失败"]);
            }
        } catch (\exception $exception) {
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }

    /**
     * 熄灭广告（隐藏）
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function slake(int $id)
    {
        $user = $this->user;
        try {
            if ((new GoodsService())->slake($id, $user->id)) {
                return response()->json(['code' => 0, 'msg' => "操作成功"]);
            } else {
                return response()->json(['code' => 107, 'msg' => "操作失败"]);
            }

        } catch (\exception $exception) {
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }
}
