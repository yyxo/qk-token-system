<?php

namespace App\Service;

use App\Model\Assets;
use App\Model\Goods;
use App\Model\GoodsType;
use App\Model\Orders;
use App\Model\Users;
use exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * 创建交易
     * @param int $goodsId
     * @param float $amount
     * @param int $tradeType
     * @param int $uid
     * @param string $ip
     * @return mixed
     * @throws exception
     */
    public function create(int $goodsId,$amount, int $tradeType, int $uid, string $ip)
    {
        $amount = bcadd($amount,0,4);//只算4位小数
        //执行事务
        DB::beginTransaction();
        try {
            $info = $this->beforeCreate($goodsId, $amount, $tradeType, $uid);//验证广告状态，获取广告信息
            $goodsInfo = $info['goodsInfo'];//广告信息
            $goodsType = $info['goodsType'];//资产类型相关数据

            /**订单信息完善***********************************************************************/
            $order = new Orders();
            $order->goods_id = $goodsInfo->id;
            $order->goods_name = $goodsType->from_assets_type . '/' . $goodsType->to_assets_type;
            $order->trade_type = $goodsInfo->trade_type; //交易类型
            $order->goods_type = $goodsInfo->type; //资产交换类型
            if ($goodsInfo->trade_type == Goods::TYPE_BUY) {
                //买单,用户出售给挂单者
                $order->sell_uid = $uid; //卖家ID（下单者）
                $order->buy_uid = $goodsInfo->uid; //买家id(挂单者)
            } else if($goodsInfo->trade_type == Goods::TYPE_SELL) {
                //卖单，用户购买挂单者的资产
                $order->sell_uid = $goodsInfo->uid;//卖家（挂单者）
                $order->buy_uid = $uid;//买家（下单者）
            }
            else
            {
                throw new exception("广告状态不正确，不能交易", 182);
            }
            $order->amount = $amount;//交易数量
            $order->price = $goodsInfo->price;//订单单价为广告单价
            $order->random = rand(100000, 999999);
            $order->total_price = bcmul($amount, $goodsInfo->price, 8);
            $order->status = Orders::STATUS_UNPAID;//订单状态为"待付款"

            //生成订单
            $order->save();
            //广告信息更新
            $goodsInfo->amount = bcsub($goodsInfo->amount, $amount, 8);
            //可购买数量小于限购，熄灭广告
            if($goodsInfo->amount < $goodsInfo->min_buy){
                $goodsInfo->shine_time = date("Y-m-d H:i:s", time() - 1);
            }
            //如果广告剩余数量为0，订单完成
            if($goodsInfo->amount<=0){
                $goodsInfo->status = Goods::STATUS_DONE;
            }

            $goodsInfo->save();

            if ($goodsType->is_storage == 1) {
                //托管交易直接完成
                if(!$this->shipOperate($order,$goodsType,$goodsInfo)){
                    DB::rollback();
                    throw new Exception('操作失败', 1001);
                };
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return $order->id;
    }

    /**
     * 创建订单前验证客户端提交的数据和广告状态
     * @param int $goodsId
     * @param float $amount
     * @param int $tradeType
     * @param int $uid
     * @return mixed
     * @throws exception
     */
    public function beforeCreate(int $goodsId,$amount, int $tradeType, int $uid)
    {
        if (!$goodsId || !$amount || !$tradeType) {
            throw new exception("缺少必要参数", 118);
        }

        //获取该单详情
        $goodsModel = new Goods();
        $goodsInfo = $goodsModel->where('id', $goodsId)
            ->where('trade_type', $tradeType)
            ->where('status', Goods::STATUS_PUBLISHING)
            ->lockForUpdate()
            ->first();

        if (!$goodsInfo) {
            throw new exception("该单已完成或不存在", 119);
        }
        //资产类型数据
        $GoodsType = GoodsType::find($goodsInfo->type);
        if ($GoodsType->lock!=1) {
            throw new exception("系统维护，该类型广告已暂停下单", 189);
        }

        //没有点亮的广告不能交易
        if (strtotime($goodsInfo->shine_time) < time()) {
            throw new exception("广告状态不正确，不能交易", 182);
        }

        //不能和自己交易
        if ($uid == $goodsInfo->uid) {
            throw new exception("不能下单自己的广告", 122);
        }

        //广告剩余数量不足
        if ($amount > $goodsInfo->amount) {
            throw new exception("该单余量不足", 121);
        }

        return ['goodsInfo' => $goodsInfo, 'goodsType' => $GoodsType];
    }

    /**订单完成
     * @param $order
     * @param $goodsType
     * @param $goodsInfo
     * @return bool
     * @throws exception
     */
    public function shipOperate($order,$goodsType,$goodsInfo){
        $assets_from_type = Assets::where('assets_name',$goodsType->from_assets_type)->first();
        $assets_to_type = Assets::where('assets_name',$goodsType->to_assets_type)->first();
        if(!$assets_from_type || !$assets_to_type){
            throw new exception("资产类型异常", 180);
        }
        $order->status = Orders::STATUS_DONE;
        $order->pay_time =date('Y-m-d H:i:s');
        $order->receivables_time =date('Y-m-d H:i:s');
        $order->finish_time =date('Y-m-d H:i:s');
        $order->save();//保存订单信息
        Users::where('id',$order->buy_uid)->increment('order_amount');//买家成交数+1
        Users::where('id',$order->sell_uid)->increment('order_amount');//卖家成交数+1
        //广告是买家,扣除托管to
        if($goodsInfo->uid == $order->buy_uid){
            if ($goodsType->is_storage == 1) {
                //买家扣除冻结
                BalancesService::freezeChange($order->buy_uid,$assets_to_type->id,-$order->total_price,'order-buy-to-done','购买资产扣除冻结',$order->goods_id,$order->id);
            }
        }else{
            if ($goodsType->is_storage == 1) {
                //买家余额扣除资产(to)
                BalancesService::BalancesChange($order->buy_uid, $assets_to_type->id, $assets_to_type->assets_name, -$order->total_price, 'order-buy-to-done', '购买资产扣除余额', $order->id);
            }
        }
        //广告是卖家,扣除托管from
        if($goodsInfo->uid == $order->sell_uid){
            //卖家扣除冻结
            BalancesService::freezeChange($order->sell_uid,$assets_from_type->id,-$order->amount,'order-sell-done','卖出资产扣除冻结余额',$order->goods_id,$order->id);
        }else{
            //卖家扣除资产(from)
            BalancesService::BalancesChange($order->sell_uid, $assets_from_type->id, $assets_from_type->assets_name, -$order->amount, 'order-sell-de', '卖家卖出扣除资产', $order->id);
        }

        $fee = bcmul($order->total_price,"0.02",8);//手续费

        //卖家得到99%
        $sell_total_price = bcmul($order->total_price,"0.98",8);

        //卖家卖出获得to 资产
        BalancesService::BalancesChange($order->sell_uid,$assets_to_type->id ,$assets_to_type->assets_name, $sell_total_price, 'order-sell-done', '卖家卖出获得资产', $order->id);
        //买家购买获得from 资产
        BalancesService::BalancesChange($order->buy_uid,$assets_from_type->id ,$assets_from_type->assets_name, $order->amount, 'order-buy-from-done', '购买资产获得余额', $order->id);

        //交易手续费 todo  请设置默认手续费账户，这里是设置的UID 1的账户
        BalancesService::BalancesChange(1,$assets_to_type->id ,$assets_to_type->assets_name, $fee, 'order-fee', '交易手续费', $order->id);
        return true;
    }
}
