<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsQuestionnairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_type_id')->nullable();
            $table->foreign('question_type_id')->references('id')->on('question_types');
            $table->unsignedBigInteger('questionnaire_id')->nullable();
            $table->foreign('questionnaire_id')->references('id')->on('questionnaires');
            $table->mediumText('question');
            $table->boolean('required')->default(true);
            $table->text('options')->nullable();
            $table->string('illustration')->nullable();
            $table->integer('order')->nullable();
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
        Schema::dropIfExists('questions_questionnaires');
    }
}
