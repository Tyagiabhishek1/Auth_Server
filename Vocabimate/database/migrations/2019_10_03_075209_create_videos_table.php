<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('video_id');
            $table->string('video_title')->nullable();
            $table->string('video_size')->nullable();
            $table->string('video_url')->nullable()->default('N');;
            $table->string('thumbnail_url')->nullable();
            $table->string('video_length')->nullable();
            $table->string('download_ind',1)->nullable()->default('N');
            $table->timestamp('create_dt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user_id')->nullable();
            $table->timestamp('update_dt')->nullable();
            $table->string('update_user_id')->nullable();
            $table->string('delete_ind',1)->nullable()->default('N');
            $table->integer('user_id')->references('user_id')->on('users');;
            $table->text('m3u8_url')->nullable();
            $table->string('job_success_ind',1)->nullable();
            $table->timestamp('job_success_time')->nullable();
            $table->string('thumbnail_file')->nullable();
            $table->string('video_name')->nullable();
            $table->string('upload_ind')->nullable();
            $table->string('video_type')->nullable();
            $table->string('video_expiry')->nullable();
            $table->string('is_rental')->nullable();
            $table->string('rental_period')->nullable();
            $table->string('number_time')->nullable();
            $table->string('video_provider_expiry_time')->nullable();
            $table->double('actual_price')->nullable();
            $table->double('discount_price')->nullable();
            $table->double('rental_price')->nullable();
            $table->text('video_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
