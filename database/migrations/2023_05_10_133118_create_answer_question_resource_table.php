<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswerQuestionResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answer_question_resource', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->unsignedBigInteger('questionnaire_id')->nullable();
            $table->foreign('questionnaire_id')->references('id')->on('questionnaires');
            $table->unsignedBigInteger('question_id')->nullable();
            $table->foreign('question_id')->references('id')->on('questions_questionnaires');
            $table->text('value');
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
        Schema::dropIfExists('answer_question_resource');
    }
}
