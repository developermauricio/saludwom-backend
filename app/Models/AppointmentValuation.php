<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Comment\Doc;

class AppointmentValuation extends Model
{
    use HasFactory;

    const PENDING = 1;
    const CANCELLED = 2;
    const FINISHED = 3;
    const IN_PROGRESS = 5;

    protected $guarded = ['id'];
    protected $fillable = ['valuation_id', 'doctor_id', 'schedule_hours_minutes_id', 'date', 'only_date', 'only_hour', 'only_minute', 'state', 'link_meeting_zoom', 'id_meeting_zoom', 'timezone'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function valuation()
    {
        return $this->belongsTo(Valuation::class, 'valuation_id');
    }

    public function schedulesHoursMinutes()
    {
        return $this->belongsTo(SchedulesHoursMinute::class, 'schedule_hours_minutes_id');
    }

    public static function stateColorAppointment($state)
    {
        if ($state == '1') {
            return '#f1b400';
        }
        if ($state == '2') {
            return 'red';
        }
        if ($state == '3') {
            return 'green';
        }
        return '#17a2b8';
    }

}
