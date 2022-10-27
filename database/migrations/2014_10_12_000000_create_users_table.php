<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('document')->nullable();
            $table->unsignedBigInteger('identification_type_id')->nullable();;
            $table->foreign('identification_type_id')->references('id')->on('identification_types');
            $table->date('birthday')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->enum('state', [
                \App\Models\User::ACTIVE,
                \App\Models\User::INACTIVE,
                \App\Models\User::PENDING_ACCOUNT_ACTIVATION])
                ->default(\App\Models\User::ACTIVE);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('slug');
            $table->string('picture')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')->references('id')->on('cities');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
