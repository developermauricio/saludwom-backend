<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->foreign('subscription_id')->references('id')->on('subscriptions');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->double('price_total');
            $table->mediumText('observations')->nullable();
            $table->string('invoice_id')->nullable()->comment('Factura generada por stripe');
            $table->string('payment_method')->nullable();
            $table->string('currency')->default('EUR');
            $table->double('discount')->nullable();
            $table->timestamp('pait  _at')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->foreign('coupon_id')->references('id')->on('coupons');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->enum('state', [
                \App\Models\Order::PENDING,
                \App\Models\Order::CANCELLED,
                \App\Models\Order::REJECTED,
                \App\Models\Order::ACCEPTED,
                \App\Models\Order::PREPARED
            ])->default(\App\Models\Order::PENDING);
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
        Schema::dropIfExists('orders');
    }
}
