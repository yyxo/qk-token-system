<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username',64)->unique()->comment('用户名');
            $table->unsignedInteger('invite_uid')->index()->default(0)->nullable()->comment('邀请人ID');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('1启用 2禁用');
            $table->string('salt',6)->unique()->comment('盐');
            $table->string('reg_ip',16)->comment('IP');
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
        Schema::dropIfExists('users');
    }
}
