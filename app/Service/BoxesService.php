<?php


namespace App\Service;


use App\Model\Assets;
use App\Model\Block;
use App\Model\Boxes;
use App\Model\Settings;
use App\Service\RpcService;
use Illuminate\Support\Facades\DB;

class BoxesService
{

    public function open($uid,$amount,$color,$assets_type)
    {
        $real_last_block = (new RpcService())->lastBlockHeightNumber();
        if($real_last_block < 2430000)
        {
            return false;
        }

        $box_height = bcadd($real_last_block,2);

        while (true)
        {
            $exist = Boxes::where('uid',$uid)
                ->where('height',$box_height)
                ->exists();
            if(!$exist)
                break;
            else
                $box_height++;
        }



        DB::beginTransaction();
        try {

            $box = new Boxes();
            $box->uid = $uid;
            $box->assets_id = $assets_type->id;
            $box->amount = $amount;
            $box->color = $color;
            $box->height = $box_height;
            $box->status = 0;

            BalancesService::BalancesChange($uid,$assets_type->id, $assets_type->assets_name, -$amount, "open_box", "开箱子");
            $box->save();
            DB::commit();
            return true;
        }
        catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return false;
    }

    /**
     * 更新状态
     */
    public function checkBoxStatus()
    {
        $last_block_height = Settings::where('key', 'box_block_height')->first();

        if (!$last_block_height) {
            $last_block_height = new Settings();
            $last_block_height->key = 'box_block_height';
            $last_block_height->value = 2441400;
            $last_block_height->save();
        }
        $lastBlock = $last_block_height->value;

        //获取最后一个高度
        $real_last_block = (new RpcService())->rpc('eth_getBlockByNumber', [['latest', true]]);

        if (isset($real_last_block[0]['result']['number']) && $real_last_block[0]['result']['number']) {
            $real_last_block = base_convert($real_last_block[0]['result']['number'], 16, 10) ?? 0;
        }

        echo "当前最高高度：$real_last_block\n";

        $num = 500;
        if ($real_last_block) {
            if ($lastBlock + 10 >= $real_last_block) {
                $num = 10;
            }
        }
        for ($i = 0; $i < $num; $i++) {
            //组装参数
            if ($lastBlock < 10) {
                $blockArray[$i] = ['0x' . $lastBlock, true];
            } else {
                $blockArray[$i] = ['0x' . base_convert($lastBlock, 10, 16), true];
            }

            $lastBlock++;
        }
        //获取下一个区块
        $rpcService = new RpcService();
        try{
            $blocks = $rpcService->getBlockByNumber($blockArray);
        }
        catch (\Exception $exception)
        {
            echo "请求接口超时 \n";
        }

        if ($blocks)
        {
            foreach ($blocks as $block)
            {
                if ($block['result']) {


                    $block_height = base_convert($block['result']['number'], 16, 10);
                    $block_time = base_convert($block['result']['timestamp'],16,10);
                    //太新的区块，不处理,至少要求60秒钟以上
                    if(time() - $block_time < 60)
                    {
                        break;
                    }

                    $mix = Boxes::where('height',$block_height)
                        ->where('status',0)
                        ->get();
                    $hash = strtolower($block['result']['hash']);
                    if(strlen($hash) != 66)
                        return;

                    $str = substr($hash,65,1);


                    if(strnatcmp($str,'7') <= 0)
                    {
                        echo "$block_height $hash 红色$str\n";
                        $color = 1;
                    }
                    else
                    {
                        echo "$block_height $hash 蓝色$str\n";
                        $color = 2;
                    }
                    DB::beginTransaction();
                    try {
                        foreach ($mix as $item)
                        {
                            if($color == $item->color)
                            {
                                $assets_type = Assets::where("id",$item->assets_id)->first();
                                $item->status = 1;
                                BalancesService::BalancesChange($item->uid,$assets_type->id, $assets_type->assets_name, bcmul($item->amount,'1.8',1), "box_success", "开箱子成功");
                            }
                            else
                            {
                                $item->status = 2;
                            }
                            $item->save();
                        }

                        Settings::where('key', 'box_block_height')->update(['value' => $block_height]);
                        DB::commit();
                    }
                    catch (\Exception $e) {
                        DB::rollback();
                        throw $e;
                    }
                }
            }

        }

    }
}