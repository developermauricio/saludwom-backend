<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourceFolderContendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_folder_contends', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->mediumText('description');
            $table->enum('state', [
                \App\Models\ResourceFolderContend::ACTIVE,
                \App\Models\ResourceFolderContend::INACTIVE
            ])->default(\App\Models\ResourceFolderContend::ACTIVE);
            $table->unsignedBigInteger('archive_id')->nullable();
            $table->foreign('archive_id')->references('id')->on('archives');
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
        Schema::dropIfExists('resource_folder_contends');
    }
}
