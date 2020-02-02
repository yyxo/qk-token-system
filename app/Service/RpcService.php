<?php

namespace App\Service;


class RpcService{


    /**
     * rpc
     * @param $method
     * @param $params
     * @return mixed
     */
    public function rpc($method, $params){
        $param = array();
        foreach($params as $key => $item){
            $id = rand(1, 100);
            $param[$key] = [
                'jsonrpc' => "2.0",
                "method"  => $method,
                "params"  => $item,
                "id"      => $id
            ];
        }

        $param = json_encode($param);
        $data_str = $this->curlPost($param);
        $data = json_decode($data_str, true);

        return $data;
    }


    /**
     * 获得区块
     * @param $param
     * @return mixed
     */
    public function getBlockByNumber($param){
        $block = $this->rpc('eth_getBlockByNumber', $param);
        return $block;
    }


    /**
     * 获取最后一个区块的高度
     * @return mixed
     */
    public function lastBlockHeightNumber(){
        $params = array(
            ['latest',true]
        );
        $blockHeight = $this->rpc('eth_getBlockByNumber',$params);

        $timestamp = base_convert($blockHeight[0]['result']['timestamp'],16,10);
        if($timestamp < time() -20)
            return 0;
        return base_convert($blockHeight[0]['result']['number'],16,10);
    }


    /**
     * 获取区块数组
     * @param $lastBlock
     * @return array
     */
    public function getBlockString($lastBlock){
        $blockArray = array();
        for($i = 0; $i < 20; $i++){
            $blockArray[$i] = ['0x' . base_convert($lastBlock--, 10, 16), true];
        }
        return $blockArray;
    }


    /**
     * 根据hash获取区块详情
     * @param $hash
     * @return mixed
     */
    public function getBlockByHash($hash){
        $method = 'eth_getBlockByHash';
        $param = array(
            [$hash, true]
        );
        $blockInfo = $this->rpc($method, $param);
        return $blockInfo[0];
    }


    /**
     * post请求
     * @param $data
     * @return mixed
     */
    public function curlPost($data){
        $url = env('RPC_HOST');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}
