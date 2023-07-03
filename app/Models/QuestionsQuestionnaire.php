<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionsQuestionnaire extends Model
{
    use HasFactory;
    protected $guarded = 'id';
    protected $fillable = ['question_type_id', 'questionnaire_id', 'question', 'required', 'options', 'illustration', 'order'];


    public function typeQuestion(){
        return $this->belongsTo(QuestionTypes::class, 'question_type_id')->select('id', 'name');
    }

    public function questionnaire(){
        return $this->belongsTo(Questionnaire::class);
    }

    public function answerQuestionsQuestionnaire(){
        return $this->hasOne(AnswerQuestionResource::class, 'question_id');    }
}
