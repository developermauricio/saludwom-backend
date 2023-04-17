<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourceFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_folders', function (Blueprint $table) {
            $table->id();
            $table->string('folder');
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->enum('state', [
                \App\Models\ResourceFolder::ACTIVE,
                \App\Models\ResourceFolder::INACTIVE
            ])->default(\App\Models\ResourceFolder::ACTIVE);
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
        Schema::dropIfExists('resource_folders');
    }
}
