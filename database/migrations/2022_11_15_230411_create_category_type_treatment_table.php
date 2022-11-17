<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryTypeTreatmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_type_treatment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_treatment_id')->nullable();
            $table->foreign('type_treatment_id')->references('id')->on('type_treatments');
            $table->unsignedBigInteger('category_treatment_id')->nullable();
            $table->foreign('category_treatment_id')->references('id')->on('category_treatments');
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
        Schema::dropIfExists('category_type_treatment');
    }
}
