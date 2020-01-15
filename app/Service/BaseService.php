<?php

namespace App\Service;

use App\Exceptions\BusinessException;
use App\Model\Settings;
use exception;


/**
 * service 基类
 * Class BaseService
 * @package App\Service
 */
class BaseService{


    /**
     * 判断是否暂停功能
     * @param $key
     * @return bool
     * @throws exception
     */
    public static function isLockByKey($key){
        //判断是否暂停功能
        $settingModel = new Settings();
        $orderLock = $settingModel->getValueByKey($key);
        if($orderLock && $orderLock == 2){
            $msgKey = $key . "_msg";
            $orderLockMsg = $settingModel->getValueByKey($msgKey);
            throw new BusinessException($orderLockMsg, 189);
        }else{
            return true;
        }
    }


    /**
     * GET方式请求
     * @param $url
     * @param array $headers
     * @return bool|string
     */
    public function getData($url, $headers = []){
        $ch = curl_init();

        // 取消SSL证书检验
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        if(!empty($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        if(curl_errno($ch)){
            return false;
        }else{
            return $result;
        }
    }


    /**
     * POST方式请求
     * @param mixed $data 需要发送的数据
     * @param string $url url
     * @param array $headers
     * @return mixed $result
     */
    public function postData($data, $url, $headers = []){

        $ch = curl_init();
        // 取消SSL证书检验
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if(curl_errno($ch)){
            echo curl_errno($ch) . ':' . curl_error($ch);
            return false;
        }else{
            return $result;
        }
    }


    /**
     * 计算2个时间差剩余时间
     * @param $day1 时间戳
     * @param $day2 时间戳
     * @return string
     */
    function timeDifference($day1, $day2){

        $secs = bcsub($day1, $day2);

        $result = '';

        if($secs >= 86400){
            $days = floor($secs / 86400);
            $secs = $secs % 86400;
            $result = $days . ' 天';
            if($secs > 0){
                $result .= ' ';
            }
        }

        if($secs >= 3600){
            $hours = floor($secs / 3600);
            $secs = $secs % 3600;
            $result .= $hours . ' 小时';
            if($secs > 0){
                $result .= ' ';
            }
        }

        if($secs >= 60){
            $minutes = floor($secs / 60);
            $secs = $secs % 60;
            $result .= $minutes . ' 分钟';
            if($secs > 0){
                $result .= ' ';
            }
        }

        if($secs > 0){
            $result .= $secs . ' 秒';
        }else{
            $result = false;
        }
        return $result;
    }


    /**
     * 定时任务是否锁定
     */
    protected function isLock($key){
        return file_exists(storage_path($key)) ? true : false;
    }


    /**
     * 锁定定时任务
     */
    protected function lock($key){
        file_put_contents(storage_path($key), '1');
    }


    /**
     * 解锁定时任务
     */
    protected function unlock($key){
        if($this->isLock($key)){
            unlink(storage_path($key));
        }
    }
}
