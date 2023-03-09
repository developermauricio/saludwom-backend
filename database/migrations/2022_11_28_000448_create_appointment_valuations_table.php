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
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->foreign('doctor_id')->references('id')->on('doctors');
            $table->unsignedBigInteger('schedule_hours_minutes_id')->nullable();
            $table->foreign('schedule_hours_minutes_id')->references('id')->on('schedule_hours_minutes');
            $table->timestamp('date');
            $table->string('only_date');
            $table->string('timezone');
            $table->string('only_hour');
            $table->string('only_minute');
            $table->string('link_meeting_zoom')->nullable();
            $table->string('id_meeting_zoom')->nullable();
            $table->enum('state', [
                \App\Models\AppointmentValuation::PENDING,
                \App\Models\AppointmentValuation::CANCELLED,
                \App\Models\AppointmentValuation::FINISHED,
                \App\Models\AppointmentValuation::IN_PROGRESS
            ])->default(\App\Models\AppointmentValuation::PENDING);
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
