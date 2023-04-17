<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRFolderContentsTreatmetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('r_folder_contents_treatmets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_treatment_id')->nullable();
            $table->foreign('type_treatment_id')->references('id')->on('type_treatments');
            $table->unsignedBigInteger('resource_folder_contend_id')->nullable();
            $table->foreign('resource_folder_contend_id')->references('id')->on('resource_folder_contends');
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
        Schema::dropIfExists('r_folder_contents_treatmets');
    }
}
