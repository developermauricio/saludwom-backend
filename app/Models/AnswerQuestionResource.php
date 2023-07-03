<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerQuestionResource extends Model
{
    use HasFactory;
    protected $table = 'answer_question_resource';

    public function question(){
        return $this->belongsTo(QuestionsQuestionnaire::class, 'question_id');
    }
}
