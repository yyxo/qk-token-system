<?php

/**
 * Created by PhpStorm.
 * User: woshi
 * Date: 2018/12/26
 * Time: 17:31
 */

namespace App\Service;

use App\Exceptions\BusinessException;
use App\Model\Assets;
use App\Model\Balances;
use App\Model\BalancesLogs;


class BalancesService{


    /**
     * 获取指定资产余额
     * @param int $uid
     * @param int $assets_id
     * @return float|mixed|null
     */
    public static function getBalance(int $uid, int $assets_id){
        return self::getBalanceData($uid, $assets_id)->amount ?? null;
    }

    /**
     * 获取指定资产余额
     * @param int $uid
     * @param string $assets_name
     * @return float|mixed|null
     */
    public static function getBalanceByName(int $uid, string $assets_name){
        return self::getBalanceDataByName($uid, $assets_name)->amount ?? null;
    }


    /**
     * 获取余额orm
     * @param int $uid
     * @param int $assets_id
     * @return Balances|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    public static function getBalanceData(int $uid, int $assets_id){
        $data = Balances::where('uid', $uid)
            ->where('assets_id', $assets_id)
            ->lockForUpdate()
            ->first();

        if(!$data){
            //获取资产类型
            $assets = Assets::find($assets_id);
            if($assets){
                $data = new Balances();
                $data->uid = $uid;
                $data->assets_id = $assets_id;
                $data->name = $assets->assets_name;
                $data->amount = 0;
                $data->freeze_amount = 0;
                $data->save();
            }
        }
        return $data;
    }


    /**
     * 获取余额orm
     * @param int $uid
     * @param string $assets_name
     * @return Balances|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    public static function getBalanceDataByName(int $uid, string $assets_name){
        $data = Balances::where('uid', $uid)
            ->where('name', $assets_name)
            ->lockForUpdate()
            ->first();

        if(!$data){
            //获取资产类型
            $assets = Assets::where('assets_name',$assets_name)->first();
            if($assets){
                $data = new Balances();
                $data->uid = $uid;
                $data->assets_id = $assets->id;
                $data->name = $assets->assets_name;
                $data->amount = 0;
                $data->freeze_amount = 0;
                $data->save();
            }
        }
        return $data;
    }


    /**
     * 新的资产变动方法
     * @param int $uid
     * @param int $assets_id
     * @param string $assets_type
     * @param float $amount
     * @param string $operate_type
     * @param null $remark
     * @param null $data_id
     * @param null $tx_hash
     * @param null $transaction_id
     * @param null $trade_type
     * @return int
     * @throws exception
     */
    public static function BalancesChange(int $uid, int $assets_id, string $assets_type, float $amount, string $operate_type, $remark = null, $data_id = null, $tx_hash = null, $transaction_id = null, $trade_type = null){
        $info = self::changeWithoutLog($uid, $assets_id, $amount, $trade_type);

        //写入日志
        $balancesLogs = new BalancesLogs();
        $balancesLogs->assets_id = $assets_id;
        $balancesLogs->assets_name = $assets_type;
        $balancesLogs->uid = $uid;
        $balancesLogs->tx_id = $transaction_id ?: 0;
        $balancesLogs->operate_type = $operate_type;
        $balancesLogs->amount = $amount;
        $balancesLogs->amount_before_change = $info['amount_before_change'];
        $balancesLogs->tx_hash = $tx_hash;
        $balancesLogs->ip = \Request::getClientIp();
        $balancesLogs->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 255, 'utf-8') : '';
        $balancesLogs->remark = $remark;
        $balancesLogs->save();
        return $balancesLogs->id;
    }


    /**
     * 改变余额不写日志
     * @param int $uid
     * @param int $assets_id
     * @return float|mixed
     * @throws exception
     */
    public static function changeWithoutLog(int $uid, int $assets_id, float $amount, $trade_type = null){

        //当前余额
        $balance = self::getBalanceData($uid, $assets_id);
        $amount_before_change = $balance->amount;
        $amount_after_change = bcadd($amount_before_change, $amount, 18);
        //下单方为买的情况，手续费可扣为负数
        if($trade_type != 2){
            //如果是扣除操作，结果小于0则报错
            if(bccomp($amount, 0, 18) < 0){
                if(bccomp($amount_after_change, 0, 18) < 0){
                    throw new BusinessException('余额不足', 175);
                }
            }
        }
        $balance->amount = $amount_after_change;
        $balance->save();
        return ['amount_before_change' => $amount_before_change, 'amount_after_change' => $amount_after_change];
    }
}
