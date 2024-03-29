<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->mediumText('path_file');
            $table->string('name_file');
            $table->string('type_file');
            $table->string('storage');
            $table->enum('state', [
                \App\Models\Archive::ACTIVE,
                \App\Models\Archive::INACTIVE
            ])->default(\App\Models\Archive::ACTIVE);
            $table->morphs('archiveable');
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
        Schema::dropIfExists('archives');
    }
}
