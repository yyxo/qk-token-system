<?php

namespace App\Service;

use App\Model\Assets;
use App\Model\Goods;
use App\Model\GoodsType;
use App\Model\Orders;
use App\Model\Settings;
use App\Model\Users;
use exception;
use Illuminate\Support\Facades\DB;

class GoodsService
{
    /**发布广告
     * @param int $uid
     * @param int $tradeType
     * @param float $totalAmount
     * @param float $price
     * @param $goods_type
     * @param float $minBuy
     * @param int $isAnonymous
     * @return int
     * @throws exception
     */
    public function publish(int $uid, int $tradeType,$totalAmount,$price,$goods_type,$minBuy,int $isAnonymous)
    {
        //判断参数
        if (!$uid || !$tradeType || !$totalAmount || !$price || !$minBuy || !$isAnonymous) {
            throw new exception("缺少必要参数", 50);
        }
        if($totalAmount > $goods_type->goods_max_amount){
            throw new exception("发布最大数量".$goods_type->goods_max_amount, 50);
        }
        if($totalAmount < $goods_type->goods_min_amount){
            throw new exception("发布最小数量".$goods_type->goods_min_amount, 50);
        }
        if($minBuy > $totalAmount){
            throw new exception("限购不能大于出售数量", 50);
        }
        if($minBuy < $goods_type->order_min_amount){
            throw new exception("限购不能小于".$goods_type->order_min_amount, 50);
        }
        if($price > $goods_type->max_price){
            throw new exception("请按最高限价".$goods_type->max_price, 50);
        }

        $user_good_amount = Goods::where([['type',$goods_type->id],['status',1],['amount','>',0],['trade_type',$tradeType],['uid','=',$uid]])
            ->lockForUpdate()
            ->count();
        if($user_good_amount > 2)
            throw new exception("每个用户同时只能发布2个广告", 51);

        if($tradeType == 1)
            $search_tradeType = 2;
        else
            $search_tradeType = 1;
        $prcie_where = $search_tradeType==1?">=":"<=";
        $prcie_desc = $search_tradeType==1?"desc":"asc";

        $OrderService = new OrderService();

        //匹配交易
        while (true)
        {
            $limit = (int)$totalAmount/2+1;
            if (bccomp($totalAmount, 0, 8) <= 0) {
                //数量小于等于0，退出循环
                break;
            }
            if($limit > 100)
            {
                $limit = 100;
            }
            $passive_goods = Goods::where([['type',$goods_type->id],['status',1],['amount','>',0],['trade_type',$search_tradeType],['uid','<>',$uid]])
                ->where('price',$prcie_where,$price)
                ->where('shine_time', '>=', date("Y-m-d H:i:s", time()))
                ->orderBy('price',$prcie_desc)
                ->orderBy('updated_at', 'asc')
                ->take($limit)
                ->get();

            if(count($passive_goods) == 0)
            {
                break;
            }
            foreach ($passive_goods as $passive_good) {
                //如果这个订单的数量小于广告剩余数量
                if($totalAmount <= $passive_good->amount)
                {
                    $order_amount = $totalAmount;
                }
                else
                {
                    $order_amount = $passive_good->amount;
                }

                $orderId = $OrderService->create($passive_good->id, $order_amount, $search_tradeType, $uid, \Request::getClientIp());
                if($orderId)
                {
                    $totalAmount = $totalAmount-$order_amount;
                }
                //匹配完成，跳出循环
                if($totalAmount <= 0)
                {
                    break;
                }
            }


        }

        if($totalAmount == 0)
        {
            return $orderId;
        }


        DB::beginTransaction();
        try {
            $assets_from_type = Assets::where('assets_name',$goods_type->from_assets_type)->first();
            $assets_to_type = Assets::where('assets_name',$goods_type->to_assets_type)->first();
            if(!$assets_from_type || !$assets_to_type){
                throw new exception("资产类型异常", 180);
            }
            $goods = new Goods();
            $time = time();
            //写入数据库
            $goods->uid = $uid;
            $goods->trade_type = $tradeType;
            $goods->amount = $totalAmount;
            $goods->total_amount = $totalAmount;
            $goods->min_buy = $minBuy;
            $goods->price = $price;
            $goods->type = $goods_type->id;
            $goods->created_at = date("Y-m-d H:i:s", $time);
            $goods->shine_time = date("Y-m-d H:i:s", $time + 86400 * 365);
            $goods->status = Goods::STATUS_PUBLISHING;//取消托管资产环节,买单卖单状态都设为发布中
            $goods->is_anonymous = $isAnonymous;
            //保存到数据库
            $goods->save();
            if ($tradeType == Goods::TYPE_SELL) {
                //如果是卖单，需要托管资产，余额不足流程将会被中止
                BalancesService::BalancesChange($uid, $assets_from_type->id,$assets_from_type->assets_name, -$goods->amount, 'publish-goods-sell', '发布出售广告扣除卖家资产', $goods->id);
                //增加冻结余额
                BalancesService::freezeChange($uid,$assets_from_type->id,$goods->amount,'publish-goods-sell','发布出售广告增加卖家冻结资产',$goods->id);
            } else {
                //如果是买单托管交易,则买家也需要托管资产，余额不足流程将会被中止，改为直接扣除
                if ($goods_type['is_storage'] == 1) {

                    BalancesService::BalancesChange($uid, $assets_to_type->id,$assets_to_type->assets_name, -bcmul($goods->price, $goods->amount, 8), 'publish-goods-buy', '发布购买广告扣除买家资产', $goods->id);
                    //增加冻结余额
                    BalancesService::freezeChange($uid,$assets_to_type->id,bcmul($goods->price, $goods->amount, 8),'publish-goods-buy','发布购买广告增加卖家冻结资产',$goods->id);
                }
            }
            DB::commit();
            return $goods->id;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
    /**
     * 获取广告资产交易类型
     */
    public function getGoodsType()
    {
        $data = GoodsType::where('lock',1)->get();
        $types = [];
        foreach ($data as $v) {
            $setting = [];
            $setting['from'] = $v->from_assets_type;
            $setting['to'] = $v->to_assets_type;
            $setting['goods_max_amount'] = $v->goods_max_amount;
            $setting['goods_min_amount'] = $v->goods_min_amount;
            $setting['order_min_amount'] = $v->order_min_amount;
            $setting['required_address'] = $v->required_address;
            $setting['is_storage'] = $v->is_storage ?? 1;
            $setting['is_light'] = $v->is_light;
            $types[$v->id] = $setting;
        }
        return $types;
    }

    /**
     * 撤单
     * @param int $uid
     * @param int $goodsId
     * @return bool
     * @throws exception
     */
    public function cancel(int $uid, int $goodsId)
    {
        //判断参数
        if (!$uid || !$goodsId) {
            throw new exception("缺少必要参数", 50);
        }

        DB::beginTransaction();
        try {
            //获取广告信息
            $goodsInfo = Goods::where([['id', '=', $goodsId], ['uid', '=', $uid]])->lockForUpdate()->first() ?? null;

            //广告不存在
            if (!$goodsInfo) {
                throw new exception("广告不存在", 129);
            }
            //库存为0不能撤单
            if ($goodsInfo->amount == 0) {
                throw new exception("非法操作", 122);
            }
            //广告状态错误
            if ($goodsInfo->status!=1) {
                throw new exception("非法操作", 122);
            }


            //查询该商品是否还有进行中的订单
            $haveOrders = Orders::where([['goods_id', '=', $goodsId], ['status', '!=', 5], ['status', '!=', 6], ['status', '!=', 7]])->count();
            if ($haveOrders>0) {
                //有订单的时候不能撤单
                throw new exception("此广告下还有未完成交易的订单，您可以先“熄灭”广告", 180);
            }
            $goods_type = $this->getGoodsType()[$goodsInfo->type];
            $assets_from_type = Assets::where('assets_name',$goods_type['from'])->first();
            $assets_to_type = Assets::where('assets_name',$goods_type['to'])->first();
            if(!$assets_from_type || !$assets_to_type){
                throw new exception("资产类型异常", 180);
            }
            if ($goodsInfo->trade_type == Goods::TYPE_SELL) {

                //扣除冻结余额
                BalancesService::freezeChange($uid,$assets_from_type->id,-$goodsInfo->amount,'cancel-goods-sell','撤销出售广告减少冻结',$goodsInfo->id);
                //如果是挂卖单，退回from资产
                BalancesService::BalancesChange($uid, $assets_from_type->id,$assets_from_type->assets_name, $goodsInfo->amount, 'cancel-goods-sell', '撤销出售广告返还买家资产', $goodsInfo->id);
            } elseif ($goodsInfo->trade_type == Goods::TYPE_BUY) {
                //如果是买单，且为托管交易,退回to资产
                if ($goods_type['is_storage'] == 1) {
                    BalancesService::freezeChange($uid,$assets_to_type->id,-bcmul($goodsInfo->price, $goodsInfo->amount, 8),'cancel-goods-buy','撤销购买广告扣除冻结',$goodsInfo->id);
                    BalancesService::BalancesChange($uid, $assets_to_type->id,$assets_to_type->assets_name,bcmul($goodsInfo->price, $goodsInfo->amount, 8), 'cancel-goods-buy', '撤销购买广告返还买家资产', $goodsInfo->id);
                }
            }
            //修改订单状态为撤单成功
            $goodsInfo->status = Goods::STATUS_CANCELED;
            $goodsInfo->save();

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * 点亮广告
     * @param $id
     * @param $shine_time
     * @param $uid
     * @return bool
     * @throws exception
     */
    public function shine($id, $uid,$shine_time=8760)
    {
        $goods = Goods::where("id", $id)
            ->where("uid", $uid)
            ->where("status", 1)
            ->first();
        if (empty($goods)) {
            throw new exception("非法操作", 122);
        }
        //需要认证商家才能发布qki<-->usd相关广告
        $user = Users::find($uid);
        //判断是否超出最高发布价格
        $goods_type = GoodsType::select('max_price','is_storage','from_assets_type','to_assets_type','is_light')->where('id',$goods->type)->first();
        if($goods_type->is_light==2){
            throw new exception("该广告类型已关闭点亮", 108);
        }

        if($goods->type  == 1)
        {
            throw new exception('广告类型错误', 112);
        }


        $max_price = $goods_type->max_price??99999;
        if($goods->price > $max_price){
            throw new exception("价格不能大于".$max_price, 107);
        }
        //判断用户是否异常
        if ($user->status != 1) {
            throw new exception('帐号异常，无法发布广告', 112);
        }

        //剩余数量小于最小购买数时，不允许点亮
        if ($goods->amount < $goods->min_buy) {
            throw new exception("剩余数量不足", 122);
        }

        if($goods->amount>0){
            $time = time();
            $goods->shine_time = date("Y-m-d H:i:s", $time + $shine_time * 60 * 60);
            $goods->save();
        }

        return $goods;
    }

    /**
     * 熄灭广告,把点亮时间设置为当前的前1秒
     * @param $id
     * @param $uid
     * @return bool
     * @throws exception
     */
    public function slake($id, $uid)
    {
        $goods = Goods::where("id", $id)
            ->where("uid", $uid)
            ->where("status", 1)
            ->first();
        if (empty($goods)) {
            throw new exception("非法操作", 122);
        }
        $goods->shine_time = date("Y-m-d H:i:s", time() - 1);
        return $goods->save();
    }
}
