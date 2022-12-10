<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->mediumText('description')->nullable();
            $table->double('discount');
            $table->unsignedBigInteger('create_user_id')->nullable();
            $table->foreign('create_user_id')->references('id')->on('users');
            $table->timestamp('date_expiration')->nullable();
            $table->integer('limit_use')->default(1);
            $table->json('except_plans')->nullable();
            $table->enum('state', [
                \App\Models\Coupon::ACTIVE,
                \App\Models\Coupon::INACTIVE
            ])->default(\App\Models\Coupon::ACTIVE);
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
        Schema::dropIfExists('coupons');
    }
}
