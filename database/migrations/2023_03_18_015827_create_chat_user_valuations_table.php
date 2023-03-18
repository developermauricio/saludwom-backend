<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatUserValuationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_user_valuations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_channel_id')->nullable();
            $table->foreign('chat_channel_id')->references('id')->on('chat_channels');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('online')->default(\App\Models\ChatUserValuation::OFFLINE);
            $table->boolean('receive_notification')->default(true);
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
        Schema::dropIfExists('chat_user_valuations');
    }
}
