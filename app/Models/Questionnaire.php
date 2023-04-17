<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;
    const ACTIVE = 1;
    const INACTIVE = 2;

    protected $guarded = 'id';
    protected $fillable = ['id','name', 'description'];

    public function treatments(){
        return $this->belongsToMany(TypeTreatment::class, 'questionnaire_treatment', 'questionnaire_id', 'type_treatment_id');
    }
    public function questions(){
        return $this->hasMany(QuestionsQuestionnaire::class)
            ->orderBy('order')
            ->select('id', 'questionnaire_id', 'question_type_id', 'question', 'required', 'options', 'illustration', 'order');
    }

}
