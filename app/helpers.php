<?php


/**
 * 16进制转10进制
 * @param string $hex
 * @return int|string
 */
function HexDec2(string $hex){
    $dec = 0;
    $len = strlen($hex);
    for($i = 1; $i <= $len; $i++){
        $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
    }
    return $dec;
}


/**
 * inarray不区分大小写
 * @param $str
 * @param $address_arr
 * @return bool
 */
function addressInArray($str, $address_arr){
    foreach($address_arr as &$v){
        $v = strtolower($v);
    }
    return in_array(strtolower($str), $address_arr);
}


/**
 * 小数小于0.0001并去掉多余0问题
 * @param $num
 * @return mixed
 */
function float_format($num){
    $num = explode('.', $num);
    if(count($num) == 1){
        return $num[0];
    }
    $de = $num[1];
    $de = rtrim($de, 0);
    if(strlen($de) > 0){
        return $num[0] . '.' . $de;
    }else{
        return $num[0];
    }
}


/**
 * 获取客户端IP
 */
function getClientIp(){
    return $_SERVER['HTTP_ALI_CDN_REAL_IP'] ?? \Request::getClientIp();
}
