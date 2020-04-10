<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->integer('uid')->unsigned()->index()->comment('用户uid');
            $table->tinyInteger('trade_type')->unsigned()->index()->comment('1买 2卖');
            $table->unsignedDecimal('amount', 18, 8)->default(0)->comment('余额');
            $table->unsignedDecimal('total_amount', 18, 8)->default(0)->comment('总额');
            $table->unsignedDecimal('min_buy', 18, 8)->default(0)->comment('最小购买');
            $table->unsignedDecimal('price', 18, 8)->default(0)->comment('单价');
            $table->tinyInteger('status')->unsigned()->default(0)->comment('0未点亮 1挂单中   2完成  3撤单 4撤单成功');
            $table->integer('type')->unsigned()->default(1)->comment('类型 对应goods_type ID');
            $table->unsignedTinyInteger('is_anonymous')->default(1)->comment('是否匿名，1不匿名，2匿名');
            $table->dateTime('shine_time')->nullable()->comment('点亮时间');
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
        Schema::dropIfExists('goods');
    }
}
