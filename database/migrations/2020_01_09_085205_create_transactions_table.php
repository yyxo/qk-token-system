<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('from',50)->nullable()->index()->comment('转出地址');
            $table->string('to',50)->nullable()->index()->comment('转入地址');
            $table->string('hash',70)->nullable()->unique()->comment('转账hash');
            $table->string('block_hash',70)->nullable()->comment('区块hash');
            $table->unsignedInteger('block_number')->default(0)->comment('区块高度');
            $table->unsignedDecimal('gas_price',18,9)->default(0)->comment('矿工费');
            $table->decimal('amount',26,18)->default(0)->comment('数量');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态，1默认，2已处理');
            $table->unsignedTinyInteger('tx_status')->default(1)->comment('交易状态，1成功，0失败');
            $table->string('assets_type', 16)->comment('资产类型');
            $table->integer('token_id')->nullable()->comment('通证类型id');
            $table->integer('data_id')->nullable()->comment('处理对应的数据id');
            $table->string('remark')->nullable()->comment('备注');
            $table->integer('admin_id')->nullable()->comment('如果是管理员操作，则填写此字段');
            $table->string('payee', 100)->nullable()->comment('合约地址(通证)');
            $table->decimal('token_tx_amount', 26,18)->nullable()->comment('通证交易数量');
            $table->unsignedInteger('uid')->comment('用户id');
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
        Schema::dropIfExists('transactions');
    }
}
