<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('treatment');
            $table->mediumText('description')->nullable();
            $table->enum('state', [
                \App\Models\TypeTreatment::ACTIVE,
                \App\Models\TypeTreatment::INACTIVE
            ])->default(\App\Models\TypeTreatment::ACTIVE);
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
        Schema::dropIfExists('type_treatments');
    }
}
