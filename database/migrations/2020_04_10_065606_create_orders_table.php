<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->integer('goods_id')->unsigned()->default(0)->comment('商品id');
            $table->string('goods_name',32)->nullable()->comment('商品名称');
            $table->tinyInteger('trade_type')->unsigned()->default(1)->comment('交易类型 ， 1卖 2买');
            $table->tinyInteger('goods_type')->default(1)->comment("资产交易类型（对应广告中的type）");
            $table->integer('sell_uid')->unsigned()->default(0)->index()->comment('卖方');
            $table->integer('buy_uid')->unsigned()->default(0)->index()->comment('买方');
            $table->unsignedDecimal('amount', 18, 8)->default(0.00000000)->comment('数量');
            $table->unsignedDecimal('price', 18, 8)->default(0.00)->comment('单价');
            $table->unsignedDecimal('total_price', 18, 8)->default(0.00)->comment('总价');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('具体类型Model里面定义');
            $table->integer('random')->unsigned()->comment('随机数');
            $table->dateTime('pay_time')->nullable()->comment('付款时间');
            $table->dateTime('receivables_time')->nullable()->comment('收款时间');
            $table->dateTime('finish_time')->nullable()->comment('完成时间');
            $table->unsignedInteger('match_goods_id')->default(0)->comment('匹配订单时被动广告ID');
            $table->string('remark',128)->default('')->comment('备注说明');
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
        Schema::dropIfExists('orders');
    }
}
