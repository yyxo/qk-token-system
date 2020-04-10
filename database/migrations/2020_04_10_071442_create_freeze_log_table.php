<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreezeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //冻结日志表
        Schema::create('freeze_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->integer('uid')->comment('用户id');
            $table->integer('goods_id')->comment('广告id');
            $table->decimal('amount', 18, 8)->comment('操作数量');
            $table->string('operate_type',32)->comment('操作类型');
            $table->char('ip',15)->comment('操作ip');
            $table->text('user_agent')->comment('user agent');
            $table->integer('assets_type_id')->comment('托管资产类型');
            $table->integer('order_id')->default(0)->comment('订单id');
            $table->string('remark')->nullable()->comment('备注');
            $table->decimal('amount_before_change', 26, 18)->default(0)->comment('操作之前冻结的余额');
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
        Schema::dropIfExists('freeze_log');
    }
}
