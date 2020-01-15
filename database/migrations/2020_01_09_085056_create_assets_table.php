<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('net_type',16)->default('qki')->comment('主网类型');
            $table->integer('decimals')->default(0)->comment('小数位数');
            $table->string('contract_address',66)->default('')->nullable()->comment('合约地址');
            $table->string('assets_name')->index()->comment('资产名称');
            $table->unsignedTinyInteger('recharge_status')->default(1)->comment('是否可充值、提现，1可以，2不能');
            $table->unique('contract_address');
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
        Schema::dropIfExists('assets');
    }
}
