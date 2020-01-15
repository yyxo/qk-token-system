<?php

namespace App\Service;

use App\Model\Assets;
use App\Model\Balance;
use App\Model\Password;
use App\Model\Users;
use App\Model\WithdrawLog;

use Exception;
use App\Exceptions\BusinessException;

use ERC20\ERC20;
use EthereumRPC\EthereumRPC;
use Illuminate\Database\QueryException;


class WithdrawService{

    /**
     * 其他资产提交提现申请并转账
     * @param int $uid
     * @param float $amount
     * @param string $userAddress
     * @param string $password
     * @param Assets $assets
     * @return bool
     * @throws Exception
     */
    public function tokenWithdrawHandle(int $uid, float $amount, string $userAddress, string $password, Assets $assets){
        $user = Users::find($uid);
        if(empty($user)){
            throw new BusinessException(trans('international.user_non_existent'), 102);
        }

        if($user->status == 2){
            throw new BusinessException(trans('international.account_blocked'), 1001);
        }

        if(bccomp($amount, 1, 8) < 0){
            throw new BusinessException("最少提现1 " . strtoupper($assets->assets_name), 174);
        }

        // 密码验证
        $passwordSha256 = Users::HashPassword($user->prefix, $user->username, $password, $user->salt);
        if(Password::where('password', $passwordSha256)->count() == 0){
            throw new BusinessException(trans('international.password_error'), 555);
        }

        //一小时内只能提现一次
        $timeRanges = date("Y-m-d H:i:s", bcsub(time(), 3600, 0));
        $withdrawLog = WithdrawLog::where("uid", $uid)->where("created_at", ">", $timeRanges)->count();
        if($withdrawLog > 0){
            throw new BusinessException(trans('international.Cash_only_once_in_an_hour'), 176);
        }

        //先保存提现记录
        \DB::beginTransaction();
        try{

            //验证用户余额
            $form_balance = BalancesService::getBalance($uid, $assets->id);
            if(bccomp($amount, $form_balance, 8) > 0){
                throw new BusinessException(trans('international.balance_not_enough'), 175);
            }

            //默认生成一条提现记录
            $withdraw = new WithdrawLog();
            $withdraw->uid = $uid;
            $withdraw->assets_type = $assets->assets_name;
            $withdraw->amount = $amount;
            $withdraw->fee = 0;
            $withdraw->status = WithdrawLog::STATUS_SUCCESS;
            $withdraw->ip = getClientIp();
            $withdraw->address = $userAddress;
            $withdraw->hour = $uid . date('YmdH');
            $withdraw->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 255, 'utf-8') : null;

            $withdraw->save();

            $BalanceService = new BalancesService();
            $BalanceService->BalancesChange($uid, $assets->id, $assets->assets_name, -$amount, "withdraw", "提现到钱包,到账" . $amount . ",手续费");

            \DB::commit();
        }catch(Exception $exception){
            \DB::rollback();
            throw new exception($exception->getMessage(), $exception->getCode());
        }catch(QueryException $exception){
            \DB::rollback();
            throw new exception(trans('international.withdrawal_failed'), 187);
        }
        if($withdraw->status != 3){
            //转账
            try{
                $txHash = $this->tokenWithdraw($amount, $userAddress, $assets->contract_address);
            }catch(Exception $exception){
                throw new exception(trans('international.tx_error') . $exception->getMessage() . " 错误code:" . $exception->getCode(), 108);
            }

            //处理提现状态
            \DB::beginTransaction();
            try{
                //记录hash
                $withdraw->tx_hash = $txHash;
                $withdraw->save();
                \DB::commit();
            }catch(Exception $exception){
                \DB::rollback();
                throw new exception($exception->getMessage(), $exception->getCode());
            }catch(QueryException $exception){
                \DB::rollback();
                throw new exception(trans('international.withdrawal_failed'), 187);
            }
        }
        return true;
    }

    /**
     * 通证提现
     * @param $num
     * @param $address
     * @param string $contract
     * @return string
     * @throws BusinessException
     * @throws \ERC20\Exception\ERC20Exception
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\ContractsException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \EthereumRPC\Exception\RawTransactionException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function tokenWithdraw($num, $address, $contract = ''){

        $url_arr = parse_url(env("WITHDRAW_RPC_HOST"));

        //实例化通证
        $geth = new EthereumRPC($url_arr['host'], $url_arr['port']);
        $erc20 = new ERC20($geth);
        $token = $erc20->token($contract);
        //托管地址（发送方）
        $payer = env('ADDRESS');
        //转账
        $data = $token->encodedTransferData($address, $num);
        $transaction = $geth->personal()->transaction($payer, $contract)
            ->amount("0")
            ->data($data);
        $transaction->gas(90000, "0.000000001");
        $txId = $transaction->send(env('PASSWORD'));

        if($txId && strlen($txId) == 66){
            return $txId;
        }else{
            throw new BusinessException(trans('international.withdrawal_failed'), 108);
        }
    }


    /**
     * 判断托管地址余额是否足够
     * @param $num
     * @param $contract
     * @return bool
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\ContractsException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public static function checkPayerBalance($num, $contract){

        $url_arr = parse_url(env("WITHDRAW_RPC_HOST"));

        //实例化通证
        $geth = new EthereumRPC($url_arr['host'], $url_arr['port']);
        $erc20 = new ERC20($geth);
        $token = $erc20->token($contract);
        //托管地址（发送方）
        $payer = env('ADDRESS');

        $payer_balance = $token->balanceOf($payer);

        if(bccomp($payer_balance, bcadd($num, 1000, 8), 0) < 0){
            return false;
        }else{
            return true;
        }
    }
}
