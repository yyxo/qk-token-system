<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalancesLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balances_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('assets_id')->comment('资产类型ID');
            $table->unsignedInteger('uid')->comment('用户id');
            $table->string('operate_type',80)->comment('操作类型');
            $table->decimal('amount',26,8)->default(0)->comment('数量');
            $table->decimal('amount_before_change',26,8)->default(0)->comment('变动前数量');
            
            //$table->string('assets_name',16)->comment('资产类型名称');
            //$table->unsignedInteger('tx_id')->nullable()->comment('交易记录id');
            //$table->string('tx_hash',66)->nullable()->unique()->comment('交易hash');
            //$table->string('ip',15)->comment('ip');
            //$table->text('user_agent')->default('');
            //$table->string('remark',100)->nullable()->comment('备注');
            
            $table->string('assets_name',30)->comment('资产类型名称');
            $table->string('tx_hash',66)->nullable()->comment('交易hash');
            $table->unsignedInteger('tx_id')->nullable()->comment('交易记录id');
            $table->string('ip',15)->nullable()->comment('ip');
            $table->text('user_agent')->nullable();
            $table->string('remark',100)->nullable()->comment('备注');
            
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
        Schema::dropIfExists('balances_logs');
    }
}
