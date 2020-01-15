<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class Assets extends Model{
    
    const ASSET_NAME_CCT = 'CCT';
    
    protected $table = 'assets';
    
    
    /**
     * 判断数据是否是CCT
     * @param type $data 待判断数据，可以是Assets模型或者字符串
     */
    public static function isCCT($data){
        
        if(is_string($data)){
            $assetName = $data;
            
        }else if(is_object($data) && $data instanceof self){
            $assetName = $data->assets_name;
        
        }else{
            return false;
        }
        
        return strtoupper($assetName) === strtoupper(self::ASSET_NAME_CCT);
    }
    
    
    /**
     * 获取CCT资产数据
     */
    public static function getAssetCCT(){
        
        // 需要 assets_name 字段不区分大小写
        
        return self::where('assets_name', self::ASSET_NAME_CCT)->first();
    }
}
