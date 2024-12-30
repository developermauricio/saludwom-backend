<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('currency')->default('EUR');
            $table->string('stripe_plan_price_id')->nullable();
            $table->mediumText('description')->nullable();
            $table->double('price');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('number_appointments')->nullable();
            $table->integer('time_interval_appointments')->default(0)->nullable();
            $table->enum('period', [
                \App\Models\Plan::WEEK,
                \App\Models\Plan::MONTH,
                \App\Models\Plan::YEAR
            ])->nullable();
            $table->string('image_background')->nullable();
            $table->enum('state', [
                \App\Models\Plan::ACTIVE,
                \App\Models\Plan::INACTIVE
            ])->default(\App\Models\Plan::ACTIVE);
            $table->integer('order')->nullable();
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
        Schema::dropIfExists('plans');
    }
}
