<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->bigIncrements('user_details_id');
            $table->integer('user_id')->references('user_id')->on('users');;
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('prefix')->nullable();
            $table->string('gender')->nullable();
            $table->datetime('dob');
            $table->integer('phone_extension')->nullable();
            $table->integer('phone');
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('zipcode')->nullable();
            $table->string('country')->nullable();
            $table->string('delete_ind',1)->nullable()->default('N');
            $table->timestamp('create_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user_id')->nullable();
            $table->timestamp('update_dt')->nullable();
            $table->string('update_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_details');
    }
}
