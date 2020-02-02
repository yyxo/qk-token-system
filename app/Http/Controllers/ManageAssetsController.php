<?php

namespace App\Http\Controllers;

use App\Model\Assets;
use App\Model\Address;
use App\Model\Balance;
use App\Model\BalanceLog;
use App\Model\BalancesLogs;

use App\Model\Users;
use App\Model\WithdrawLog;
use App\Service\AddressService;
use App\Service\BalanceService;
use App\Service\BalancesService;
use App\Service\WithdrawService;

use Illuminate\Http\Request;


/**
 * 资产管理
 */
class ManageAssetsController extends Controller{


    /**
     * 我的CCT
     */
    public function balance(){

        $assets = Assets::all();
        $balance_arr = [];
        foreach($assets as $asset){
            $balance_arr[$asset->assets_name] = BalancesService::getBalanceData($this->uid,$asset->id);
        }
        $data['balance_arr'] = $balance_arr;
        $data['title'] = '我的资产';
        return view("ManageAssets.balance", $data);
    }


    /**
     * 绑定地址
     */
    public function address(){
        $data['title'] = '绑定地址';
        return view("ManageAssets.address", $data);
    }

    /**
     * 充值页面
     */
    public function chargePage(){
        $data['title'] = "自动充值引导";
        //托管地址
        $data['officialAddress'] = 'xxxxxxxxxxxxxxxxxxxx';

        //当前账户地址
        $data['userAddress'] = '';
        $userAddress = Address::where('uid', $this->uid)->first();
        if (isset($userAddress->address) && $userAddress->address) {
            $data['userAddress'] = $userAddress->address;
        }
        return view("ManageAssets.charge-page", $data);
    }

    /**
     * 提现页面
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function withdrawPage(Request $request){
        $assets_id = $request->input('assets_id');
        $assets = Assets::find($assets_id);
        //资产不存在，直接返回
        if(empty($assets))
        {
            return back();
        }
        $data['title'] = strtoupper($assets->assets_name)."提现";
        $data['assets'] = $assets->assets_name;
        //当前账户地址
        $data['userAddress'] = '';
        $userAddress = Address::where('uid', $this->uid)->first();
        if (isset($userAddress->address) && $userAddress->address) {
            $data['userAddress'] = $userAddress->address;
        }
        //获取余额
        $data['balance'] = BalancesService::getBalance($this->uid,$assets->id);
        //提现记录
        $data['withdrawLogList'] = WithdrawLog::where("assets_type",$assets->assets_name)
            ->where('uid',$this->uid)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();
        //用户名
        $data['username'] = Users::where("id",$this->uid)->value('username');
        return view("ManageAssets.withdraw-page", $data);
    }

    /**
     * 资产明细
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function assetsLogs(Request $request){
        $assets_id = $request->input('assets_id');
        $assets = Assets::find($assets_id);
        //资产不存在，直接返回
        if(empty($assets))
        {
            return back();
        }
        $data['title'] = strtoupper($assets->assets_name)."明细";
        return view("ManageAssets.assets-logs", $data);
    }


    /**
     * 获取用户余额
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserBalance(Request $request){
        $assets_id = $request->input('assets_id');

        if($assets_id){
            $balance = float_format(BalancesService::getBalance($this->uid, $assets_id));
        }else{
            $user_balance = Balance::where('uid', $this->uid)->first();
            $balance = float_format($user_balance->amount) ? : 0;
        }

        return response()->json(['code' => 0, 'msg' => 'OK', 'data' => ['balance' => $balance]]);
    }


    /**
     * 日志
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balanceLogs(Request $request){
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 20);
        $lang = $request->input('lang', 'cn');
        $offset = ($page - 1) * $pageSize;
        $assets_id = $request->input('assets_id');

        if($assets_id){
            //除CCT外其他资产
            $logs = BalancesLogs::select(['amount', 'created_at', 'id', 'operate_type', 'remark'])
                ->where('assets_id', $assets_id)
                ->where("uid", $this->uid)
                ->orderBy('id', 'desc')
                ->offset($offset)
                ->limit($pageSize)
                ->get();
            $type_label = BalancesLogs::$operate_type_label;

            if($lang == 'en'){
                $type_label = BalancesLogs::$operate_type_label_en;
            }
        }else{
            //CCT
            $logs = BalancesLogs::select(['amount', 'created_at', 'id', 'operate_type', 'remark'])
                ->where("uid", $this->uid)
                ->orderBy('id', 'desc')
                ->offset($offset)
                ->limit($pageSize)
                ->get();
            $type_label = BalancesLogs::$operate_type_label;

            if($lang == 'en'){
                $type_label = BalancesLogs::$operate_type_label_en;
            }
        }

        if($logs){
            foreach($logs as $k => $v){
                $logs[$k]->operate_type_label = $type_label[$v->operate_type] ? : "";
                $logs[$k]->amount = float_format($v->amount);
            }
        }

        return response()->json(['code' => 0, 'msg' => 'OK', 'data' => $logs]);
    }


    /**
     * 绑定地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bindAddress(Request $request){
        $address = strtolower(trim($request->input('address')));
        $remark = trim($request->input('remark', null));

        if((substr($address, 0, 2) != "0x") || strlen($address) != 42){
            return response()->json(['code' => 3003, 'msg' => "地址格式不正确"]);
        }
        if(!preg_match("/^0x[0-9a-f]{40}$/i", $address)){
            return response()->json(['code' => 3003, 'msg' => "地址格式错误，请检查"]);
        }
        if(empty($address)){
            return response()->json(['code' => 102, 'msg' => "请填写地址"]);
        }

        $addressService = new AddressService();

        try{
            $addressService->add($this->uid, $address, $remark);
            return response()->json(['code' => 0, 'msg' => "绑定成功", 'data' => []]);
        }catch(\Exception $exception){
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }


    /**
     * 获取我的绑定地址
     */
    public function getAddress(){

        $data = Address::select('address', 'remark')->where('uid', $this->uid)->first() ? : null;

        return response()->json(['code' => 0, 'msg' => "获取成功", 'data' => $data]);
    }
    /**
     * 删除地址
     */
    public function delAddress(){
        $address = Address::where('uid', $this->uid)->first();
        if($address){
            $address->delete();
        }

        return response()->json(['code' => 0, 'msg' => "删除成功"]);
    }

    /**
     * 提现操作
     * @param Request $request
     * @param WithdrawService $withdrawService
     * @return \Illuminate\Http\JsonResponse
     */
    public function tokenWithdraw(Request $request, WithdrawService $withdrawService){
        $num = $request->input('num');
        $password = $request->input('password');
        $assets_id = $request->input('assets_id');

        if(!$assets_id){
            return response()->json(['code' => 313, 'msg' => trans('international.wrong_assets')]);
        }

        $assets = Assets::find($assets_id);

        if(empty($assets)){
            return response()->json(['code' => 313, 'msg' => trans('international.wrong_assets')]);
        }

        if(!$num){
            return response()->json(['code' => 311, 'msg' => "缺少必要参数", 'data' => []]);
        }

        //验证金额格式
        $amount_arr = explode(".", $num);

        if(!is_numeric($num) || $num <= 0 || count($amount_arr) > 2){
            return response()->json(['code' => 178, 'msg' => '提现数量有误']);
        }
        if(count($amount_arr) == 2 && strlen($amount_arr[1]) > 8){
            return response()->json(['code' => 178, 'msg' => '提现数量最多支持8位']);
        }

        //判断地址
        $address = Address::where('uid', $this->uid)->value('address');
        if(!$address){
            return response()->json(['code' => 311, 'msg' => "请先绑定地址", 'data' => []]);
        }

        //检测托管地址余额
        if(strtolower($assets->assets_name) != 'qki'){
            try{
                if(WithdrawService::checkPayerBalance($num, $assets->contract_address) === false){
                    return response()->json(['code' => 304, 'msg' => '暂停提现，请等待一个小时']);
                }
            }catch(\Exception $exception){
                return response()->json(['code' => 304, 'msg' => $exception->getMessage()]);
            }
        }

        try{
            if($withdrawService->tokenWithdrawHandle($this->uid, $num, $address, $password, $assets)){

                return response()->json(['code' => 0, 'msg' => "提现成功"]);
            }else{
                return response()->json(['code' => 101, 'msg' => "提现失败"]);
            }
        }catch(\exception $exception){
            return response()->json(['code' => $exception->getCode(), 'msg' => $exception->getMessage()]);
        }
    }
}
