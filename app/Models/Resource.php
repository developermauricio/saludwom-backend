<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;
    const PENDING = 1;
    const RESOLVED = 2;

    protected $guarded = 'id';
    protected $fillable = ['state', 'valuation_id', 'doctor_id', 'message_doctor', 'enable__touch_data'];

    public function questionnaires(){
        return $this->belongsToMany(Questionnaire::class, 'questionnaire_resource', 'resource_id', 'questionnaire_id');
    }
    public function resourceVideos(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ResourceFolderContend::class, 'video_resource', 'resource_id', 'resource_folder_content_id');
    }
    public function touchDataHumanBody(){
        return $this->hasOne(ResourceTouchData::class, 'resource_id');
    }
    public function doctor(){
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
    public function valuation(){
        return $this->belongsTo(Valuation::class, 'valuation_id');
    }
}

