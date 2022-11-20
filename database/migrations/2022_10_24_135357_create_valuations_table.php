<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValuationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valuations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->foreign('doctor_id')->references('id')->on('doctors');
            $table->unsignedBigInteger('type_treatment_id')->nullable();
            $table->foreign('type_treatment_id')->references('id')->on('type_treatments');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->foreign('subscription_id')->references('id')->on('subscriptions');
            $table->text('objectives')->nullable();
            $table->enum('state', [
                \App\Models\Valuation::PENDING_SEND_RESOURCES,
                \App\Models\Valuation::RESOURCES_SEND_FROM_DOCTOR,
                \App\Models\Valuation::PENDING_SEND_TREATMENT_FROM_DOCTOR,
                \App\Models\Valuation::IN_TREATMENT,
                \App\Models\Valuation::FINISHED,
            ])->default(\App\Models\Valuation::PENDING_SEND_RESOURCES);
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
        Schema::dropIfExists('valuations');
    }
}
