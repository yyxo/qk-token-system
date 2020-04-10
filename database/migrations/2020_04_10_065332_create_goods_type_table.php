<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_type', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->char('from_assets_type',10)->index()->comment('发布广告原始资产类型');
            $table->char('to_assets_type',10)->index()->comment('发布广告目标资产类型');
            $table->decimal('max_price', 18,8)->default(99999)->comment('最高挂单价格');
            $table->decimal('order_min_amount', 18, 10)->default(0)->comment('订单交易最小数量');
            $table->tinyInteger('required_address')->comment('是否必须绑定钱包地址,1可不绑定，2必须绑定');
            $table->tinyInteger('is_light')->unsigned()->default(1)->comment('是否支持点亮默认1可以点亮，2不可以');
            $table->unsignedTinyInteger('is_storage')->default(1)->comment('是否托管交易： 1:托管, 2: 非托管');
            $table->tinyInteger('lock')->unsigned()->default(1)->comment('是否关闭1正常2关闭');
            $table->decimal('goods_max_amount', 18, 10)->comment("广告发布最大数量");
            $table->decimal('goods_min_amount', 18, 10)->comment('广告发布最小数量');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_type');
    }
}
