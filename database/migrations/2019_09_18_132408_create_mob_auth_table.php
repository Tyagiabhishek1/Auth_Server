<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMobAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mob_auth', function (Blueprint $table) {
            $table->bigIncrements('mob_auth_id');
            $table->integer('user_id')->references('user_id')->on('users');
            $table->string('device_id');
            $table->text('access_token');
            $table->string('update_user_id')->nullable();
            $table->string('create_user_id')->nullable();;
            $table->timestamp('update_dt')->nullable();
            $table->timestamp('create_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('login_ind')->nullable()->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mob_auth');
    }
}
