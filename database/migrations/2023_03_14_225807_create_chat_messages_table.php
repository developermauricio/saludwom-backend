<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->string('type');
            $table->unsignedBigInteger('chat_channel_id')->nullable();
            $table->foreign('chat_channel_id')->references('id')->on('chat_channels');
            $table->unsignedBigInteger('send_user_id')->nullable();
            $table->foreign('send_user_id')->references('id')->on('users');
            $table->unsignedBigInteger('recipient_user_id')->nullable();
            $table->foreign('recipient_user_id')->references('id')->on('users');
            $table->timestamp('read_at')->nullable();
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
        Schema::dropIfExists('chat_messages');
    }
}
