<?php

namespace App\Http\Controllers;

use App\Model\Goods;
use App\Model\GoodsType;
use App\Model\Orders;
use App\Model\Settings;
use App\Model\Users;
use App\Service\GoodsService;
use App\Service\OrderService;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * 下单
     * @param Request $request
     * @param OrderService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, OrderService $service)
    {
        $goodsId = (int)$request->input('goods_id');
        $amount = $request->input('amount');
        if($amount < 1)
        {
            return response()->json(['code' => 107, 'msg' => "数量错误"]);
        }
        $tradeType = (int)$request->input('trade_type');
        try {

            $goodsModel = new Goods();
            $goodsInfo = $goodsModel->where('id', $goodsId)
                ->where('trade_type', $tradeType)
                ->where('status', Goods::STATUS_PUBLISHING)
                ->lockForUpdate()
                ->first();

            $good_type = GoodsType::find(2);
            if($goodsInfo->price < $good_type->max_price)
            {
                return response()->json(['code' => 107, 'msg' => "价格异常"]);
            }

            $orderId = $service->create($goodsId, $amount, $tradeType, $this->user->id, $this->getIp());
            if ($orderId) {
                $msg = $tradeType==1?"出售成功":"购买成功";
                return response()->json(['code' => 0, 'msg' => $msg, 'orderId' => $orderId]);
            } else {
                return response()->json(['code' => 107, 'msg' => "操作失败"]);
            }
        } catch (\exception $exception) {
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }
}
