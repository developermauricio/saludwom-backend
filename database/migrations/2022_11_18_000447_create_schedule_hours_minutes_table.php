<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleHoursMinutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_hours_minutes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_schedule_id')->nullable();
            $table->foreign('doctor_schedule_id')->references('id')->on('doctor_schedules');
            $table->string('hour');
            $table->string('minute');
            $table->enum('state', ['UNAGENDIZED', 'AVAILABLE', 'SELECTED', 'CANCELLED'])->default('AVAILABLE');
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
        Schema::dropIfExists('schedule_hours_minutes');
    }
}
