<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = ['doctor_id', 'date', 'comment'];

    public function schedulesHoursMinutes(){
        return $this->hasMany(SchedulesHoursMinute::class, 'doctor_schedule_id');
    }
}
