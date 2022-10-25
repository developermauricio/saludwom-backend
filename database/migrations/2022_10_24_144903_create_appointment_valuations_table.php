<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentValuationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_valuations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('valuation_id')->nullable();
            $table->foreign('valuation_id')->references('id')->on('valuations');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->text('description')->nullable();
            $table->enum('state', [
                \App\Models\AppointmentValuation::PENDING,
                \App\Models\AppointmentValuation::CANCELLED,
                \App\Models\AppointmentValuation::FINISHED
            ]);
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
        Schema::dropIfExists('appointment_valuations');
    }
}
