<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan', function (Blueprint $table) {
            $table->bigIncrements('plan_id');
            $table->string('plan_name')->nullable();
            $table->text('plan_description')->nullable();
            $table->integer('time_period_months')->nullable();
            $table->timestamp('create_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user_id')->nullable();
            $table->timestamp('update_dt')->nullable();
            $table->string('update_user_id')->nullable();
            $table->double('actual_price')->nullable();
            $table->double('discount_price')->nullable();
            $table->boolean('is_hd_available')->nullable();
            $table->boolean('is_uhd_available')->nullable();
            $table->boolean('can_download')->nullable();
            $table->string('thumb_url')->nullable();
            $table->string('del_ind',1)->nullable()->default('N');
            $table->integer('number_of_device')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan');
    }
}
