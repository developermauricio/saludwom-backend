<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->enum('state', [
                \App\Models\Resource::PENDING,
                \App\Models\Resource::RESOLVED
            ])->default(\App\Models\Resource::PENDING);
            $table->unsignedBigInteger('valuation_id')->nullable();
            $table->foreign('valuation_id')->references('id')->on('valuations');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->foreign('doctor_id')->references('id')->on('doctors');
            $table->boolean('enable_touch_data')->default(false);
            $table->boolean('assign_rating_videos')->default(false);
            $table->text('message_doctor')->nullable();
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
        Schema::dropIfExists('resources');
    }
}
