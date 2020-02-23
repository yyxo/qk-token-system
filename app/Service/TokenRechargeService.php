<?php

namespace App\Service;

use App\Model\Address;
use App\Model\Assets;
use App\Model\Balances;
use App\Model\Transactions;
use App\Model\BalancesLogs;
use App\Model\Users;


class TokenRechargeService{

    /**
     * token充值
     * @return bool
     * @throws \Exception
     */
    public function tokenCharge(){
        $qki_address_arr = explode('|', env('CHARGE_ADDRESS_ARR'));
        //获取未处理的、类型为转入、状态为成功的记录
        $logs = Transactions::whereIn('payee', $qki_address_arr)
            ->where('status', 1)
            ->where("tx_status", 1)
            ->get();

        if(!$logs){
            //无数据，终止
            echo "无充值数据\n";
            return false;
        }

        foreach($logs as $log){
            $this->doTokenRecharge($log);
        }

        echo "token充值操作执行成功\n";
        return true;
    }


    /**
     *
     * @param $log Transactions
     * @return mixed
     * @throws \Exception
     */
    public function doTokenRecharge($log){
        //根据转入地址判断用户ID
        $address = Address::where('address', $log->from)->first();
        if(!$address){
            //找不到用户，不执行操作
            echo "找不到用户\n";
            return;
        }
        if(bccomp($log->token_tx_amount, 0, 18) < 1){
            //金额小于等于0，不执行操作
            echo "金额小于等于0\n";
            return;
        }
        if(BalancesLogs::where('tx_hash', $log->hash)->count()){
            //hash是否存在于余额记录，则不执行操作
            echo "存在于余额记录\n";
            return;
        }


        //获取token类型
        $assets = Assets::find($log->token_id);

        \DB::beginTransaction();
        try{

            $data_id = BalancesService::balancesChange($address->uid, $assets->id, $assets->assets_name, $log->token_tx_amount, 'recharge', '用户充值', $log->id, $log->hash, $log->id);

            //修改交易记录信息
            $transactionLog = Transactions::lockForUpdate()->find($log->id);
            $transactionLog->status = 2;
            $transactionLog->data_id = $data_id;
            $transactionLog->uid = $address->uid;
            $transactionLog->save();

            \DB::commit();
        }catch(\Exception $e){
            \DB::rollback();
            echo "token充值失败，失败原因：" . $e->getMessage();
        }
    }
}
