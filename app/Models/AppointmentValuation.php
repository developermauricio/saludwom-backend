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
    protected $fillable = ['valuation_id','doctor_id', 'date', 'only_date', 'only_hour', 'only_minute', 'state', 'link_meeting', 'timezone'];

    public function doctor(){
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
