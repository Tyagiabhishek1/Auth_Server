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
            $table->bigIncrements('user_id');
            $table->string('login_id')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('delete_ind',1)->nullable()->default('N');
            $table->timestamp('create_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user_id')->nullable();
            $table->timestamp('update_dt')->nullable();
            $table->string('update_user_id')->nullable();
            $table->string('enabled_ind',1)->nullable()->default('N');
            $table->boolean('account_not_expired')->default(1)->change();
            $table->boolean('credentials_not_expired')->default(1)->change();
            $table->string('user_details_ind',1)->nullable()->default('N');
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
        Schema::dropIfExists('users');
    }
}
