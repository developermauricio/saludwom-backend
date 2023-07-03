<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class AnswerQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->question->id,
            'illustration' => $this->question->illustration,
            'options' => $this->question->options,
            'order' => $this->question->order,
            'question' => $this->question->question,
            'question_type_id' =>  $this->question->question_type_id,
            'questionnaire_id' => $this->question->questionnaire_id,
            'required' =>  $this->question->required,
            'value' => $this->value ?? '',
            'type_question' => $this->question->typeQuestion
        ];
    }
}
