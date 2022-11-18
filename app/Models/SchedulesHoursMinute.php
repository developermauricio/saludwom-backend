<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulesHoursMinute extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['doctor_schedule_id', 'hour', 'minute', 'state'];
    protected $table = 'schedule_hours_minutes';


}
