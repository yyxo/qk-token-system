<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('uid')->comment('用户id');
            $table->unsignedInteger('assets_id')->comment('资产类型id');
            $table->string('name',100)->comment('资产名称');
            $table->decimal('amount',26,8)->comment('数量');
            $table->decimal('freeze_amount',26,8)->comment('冻结数量');
            $table->unique(['assets_id','uid']);
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
        Schema::dropIfExists('balances');
    }
}
