<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription', function (Blueprint $table) {
            $table->bigIncrements('sub_id');
            $table->timestamp('sub_start_date')->nullable();
            $table->timestamp('sub_end_date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->foreign('plan_id')->references('plan_id')->on('plan');
            $table->string('subs_status')->nullable();
            $table->double('actual_price')->nullable();
            $table->double('discount_price')->nullable();
            $table->double('paid_price')->nullable();
            $table->timestamp('create_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user_id')->nullable();
            $table->timestamp('update_dt')->nullable();
            $table->string('update_user_id')->nullable();
            $table->string('del_ind',1)->nullable()->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription');
    }
}
