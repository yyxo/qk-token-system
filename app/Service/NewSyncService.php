<?php

namespace App\Service;

use App\Model\Assets;
use App\Model\Settings;
use App\Model\Transactions;
use App\Model\WithdrawLog;

use ERC20\ERC20;
use EthereumRPC\EthereumRPC;
use EthereumRPC\Response\TransactionInputTransfer;

use Illuminate\Support\Facades\DB;


class NewSyncService extends BaseService{

    const LOCK_KEY = 'tx-hash-sync';
    const USDT_CONTRACT_ADDRESS = '0xdac17f958d2ee523a2206206994597c13d831ec7';


    /**
     * 同步交易
     */
    public function synchronizeTransactionLogs(){

        if($this->isLock(self::LOCK_KEY)){
            echo "锁定中\n";
            return;
        }
        $this->lock(self::LOCK_KEY);
        ini_set('max_execution_time', 60);
        $end_time = time() + 55;
        while(true){
            if($end_time <= time()){
                break;
            }
            $this->syncTx();
            sleep(5);
        }
        $this->unlock(self::LOCK_KEY);

        echo "区块同步成功";
    }


    /**
     * 同步官方托管地址的交易记录
     */
    public function syncTx(){
        //获取托管账号地址
        $last_block_height = Settings::where('key', 'last_block_height')->first();

        if(!$last_block_height){
            $last_block_height = new Settings();
            $last_block_height->key = 'last_block_height';
            $last_block_height->value = 780000;
            $last_block_height->save();
        }

        $lastBlock = $last_block_height->value;

        //获取最后一个高度
        $real_last_block = (new RpcService())->rpc('eth_getBlockByNumber', [['latest', true]]);
        
        if(is_null($real_last_block)){
            echo "请求接口获取最后高度时错误 \n";
            return false;
        }
        
        if(isset($real_last_block[0]['result']['number']) && $real_last_block[0]['result']['number']){
            $real_last_block = base_convert($real_last_block[0]['result']['number'], 16, 10) ?: 0;
        }else{
            $real_last_block = 0;
        }

        echo "当前最高高度：$real_last_block\n";

        $num = 500;
        if($real_last_block){
            if($lastBlock + 10 >= $real_last_block){
                $num = 10;
            }
        }
        for($i = 0; $i < $num; $i++){
            //组装参数
            if($lastBlock < 10){
                $blockArray[$i] = ['0x' . $lastBlock, true];
            }else{
                $blockArray[$i] = ['0x' . base_convert($lastBlock, 10, 16), true];
            }

            $lastBlock++;
        }
        //获取下一个区块
        $rpcService = new RpcService();
        try{
            $blocks = $rpcService->getBlockByNumber($blockArray);
        }catch(\Exception $exception){
            echo "请求接口超时 \n";
            $this->unlock(self::LOCK_KEY);
        }
        DB::beginTransaction();
        try{

            $block_height = $last_block_height->value;
            if($blocks){
                echo "区块获取成功 \n";

                foreach($blocks as $block){
                    if($block['result']){

                        $block_time = base_convert($block['result']['timestamp'], 16, 10);
                        //太新的区块，不处理,至少要求30秒钟以上
                        if(time() - $block_time < 30){
                            break;
                        }

                        $transactions = $block['result']['transactions'];
                        //如果此区块有交易
                        if(isset($transactions) && count($transactions) > 0){
                            $timestamp = date("Y-m-d H:i:s", base_convert($block['result']['timestamp'], 16, 10));
                            foreach($transactions as $tx){
                                //保存交易
                                if(!Transactions::where('hash', $tx['hash'])->exists())
                                    $this->saveTx($tx, $timestamp);
                            }
                        }

                        $block_height = bcadd(base_convert($block['result']['number'], 16, 10), 1, 0);
                    } else{
                        $this->i = 10;
                        Settings::where('key', 'last_block_height')->update(['value' => $block_height]);
                        DB::commit();
                        echo "同步成功，当前高度:$block_height\n";
                        return false;
                    }
                }
            }

            //记录下一个要同步的区块高度
            Settings::where('key', 'last_block_height')->update(['value' => $block_height]);
            DB::commit();
            echo "同步成功，当前高度:$block_height\n";
            return true;
        }catch(\Exception $e){
            DB::rollback();
            echo $e->getMessage();
            return false;
        }
    }


    /**
     * 保存交易
     * @param $v
     * @param $timestamp
     */
    public function saveTx($v, $timestamp){

        //查询交易是否成功
        $receipt = (new RpcService())->rpc("eth_getTransactionReceipt", [[$v['hash']]]);
        if(isset($receipt[0]['result'])){
            if(isset($receipt[0]['result']['root'])){
                $tx_status = 1;
            }else{
                $tx_status = base_convert($receipt[0]['result']['status'], 16, 10);
            }
            if($tx_status != 1){
                echo "{$v['hash']}交易失败\n";
                return true;
            }
        }else{
            echo "{$v['hash']}没有回执\n";
            return true;
        }
        //写入交易记录表
        $tx = new Transactions();
        $tx->from = $v['from'];
        $tx->to = $v['to'] ?? '';
        $tx->uid = 0;
        $tx->hash = $v['hash'];
        $tx->block_hash = $v['blockHash'];
        $tx->block_number = base_convert($v['blockNumber'], 16, 10);
        $tx->gas_price = bcdiv(HexDec2($v['gasPrice']), gmp_pow(10, 18), 18);
        $tx->amount = bcdiv(HexDec2($v['value']), gmp_pow(10, 18), 18);
        $tx->created_at = $timestamp;
        $tx->tx_status = 1;

        //input可能为空
        $input = $v['input'] ?? '';

        // 通证转账
        if(substr($input, 0, 10) === '0xa9059cbb'){
            //实例化通证,获取通证小数位数
            $url_arr = parse_url(env("RPC_HOST"));
            $geth = new EthereumRPC($url_arr['host'], $url_arr['port']);
            $erc20 = new ERC20($geth);
            $token = $erc20->token($v['to']);
            $decimals = $token->decimals();
            if($decimals < 1)
                return true;
            //保存通证交易+
            $token_tx = new TransactionInputTransfer($input);
            //判断to是否为cct和usdt的合约地址，如果是则添加
            $assets = Assets::where('contract_address', $v['to'])->first();
            if($assets){
                $decimals = ($assets->decimals > 0) ? $assets->decimals : $decimals;
                $token_tx_amount = bcdiv(HexDec2($token_tx->amount), gmp_pow(10, $decimals), 18);
                //是通证，保存通证信息
                $tx->token_tx_amount = $token_tx_amount;
                $tx->assets_type = $assets->assets_name;
                $tx->token_id = $assets->id;
                $tx->payee = $token_tx->payee;
            }else{
                echo "{$v['hash']}不支持资产\n";
                return true;
            }
        }else{
            //不是通证，则是qki
            $tx->assets_type = 'qki';
        }

        $qki_address_arr = explode('|', env('CHARGE_ADDRESS_ARR'));
        if(in_array($tx->from, $qki_address_arr) || in_array($tx->to, $qki_address_arr) || in_array($tx->payee, $qki_address_arr)){
            $tx->save();
        }else{
            echo "CHARGE_ADDRESS_ARR 地址验证不通过，没有保存Transactions\n";
        }

        //同步提现记录的交易状态
        $withdraw_log = WithdrawLog::where('tx_hash', $v['hash'])->first();
        if(!empty($withdraw_log)){
            $withdraw_log->tx_status = 2;
            $withdraw_log->save();
        }

        return $tx;
    }
}
