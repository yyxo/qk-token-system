<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid');
            $table->integer('assets_id')->comment("合成的目标资产类型");
            $table->integer('amount')->comment('门票数量');
            $table->integer('height')->comment('区块高度');
            $table->integer('color')->comment('1红色 2蓝色');
            $table->tinyInteger('status')->comment('0默认 1成功 2失败');
            $table->timestamps();

            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boxes');
    }
}
