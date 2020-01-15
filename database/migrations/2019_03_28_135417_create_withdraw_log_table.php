<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uid')->comment('用户id');
            $table->char('assets_type', 10)->comment('资产类型');
            $table->string('address',255)->nullable()->comment('提现到地址');
            $table->decimal('amount', 18, 8)->default(0.00000000)->comment('数量');
            $table->decimal('fee', 18, 8)->default(0.00000000)->comment('手续费');
            $table->tinyInteger('status')->default(1)->comment('状态 1默认 2成功');
            $table->string('tx_hash',128)->unique()->nullable()->comment('哈希');
            $table->char('ip',15)->default('')->comment("操作IP");
            $table->text('user_agent')->nullable()->comment("浏览器信息");
            $table->string('msg',200)->nullable()->comment("转账错误信息");
            $table->integer('code')->nullable()->comment('转账错误码');
            $table->bigInteger('hour')->unique()->nullable()->comment('提现时刻唯一标识');
            
            $table->string('remark',255)->default('')->comment('拒绝原因');
            $table->unsignedTinyInteger('tx_status')->default(1)->comment('转账状态，1默认，2成功，3失败');
            $table->string('net_type',16)->default('qki')->comment('主网类型');
            
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdraw_log');
    }
}
