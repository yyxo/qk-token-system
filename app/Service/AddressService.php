<?php

namespace App\Service;

use App\Exceptions\BusinessException;
use App\Model\Address;
use App\Model\Users;
use App\Model\WithdrawLog;
use exception;
use App\Model\Balance;


class AddressService extends BaseService{



    /**
     * 绑定地址
     * @param $user_id
     * @param $user_address
     * @param $type
     * @param $remark
     * @return bool
     * @throws exception
     */
    public function add($user_id, $user_address, $remark){

        //每个用户最多只能绑定1个地址
        $count_address = Address::where('uid', $user_id)->count();
        if($count_address > 0){
            throw new BusinessException(trans('international.One_account'), 147);
        }

        //判断地址是否已绑定
        $is_bind = Address::where([['address', '=', $user_address]])->count();
        if($is_bind > 0){
            throw new BusinessException(trans('international.The_address_is_bound'), 115);
        }

        $address = new Address();

        if(strlen($user_address) < 30){
            throw new BusinessException(trans('international.Address_error'), 116);
        }

        $address->uid = $user_id;
        $address->address = $user_address;
        $address->remark = $remark;
        return $address->save();
    }


    /**
     * 判断用户地址是否绑定多个账户
     * @param $uid
     * @return bool
     */
    public static function checkUserAddress($uid){
        $userAddress = Address::where('uid', $uid)->first();

        if($userAddress){
            $isDuplicate = Address::where('address', $userAddress->address)->count();

            if($isDuplicate > 1){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }


    /**
     * 删除地址
     * @param $uid
     * @param $id
     * @return bool
     * @throws exception
     */
    public function delAddress($uid, $id){
        //address表id
        if(empty($id)){
            throw new BusinessException(trans('international.Network_exception'), 138);
        }
        $count = Address::where('uid', $uid)->count();
        if($count == 1){
            $userBalance = Balance::where('uid', $uid)->first();

            if($userBalance && $userBalance->qki != 0){
                throw new BusinessException(trans('international.last_address'), 122);
            }
        }

        $result = Address::where('uid', $uid)->where('id', $id)->delete();
        return $result;
    }
}
