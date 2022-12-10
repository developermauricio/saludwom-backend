<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    const WEEK = 'week';
    const MONTH = 'month';
    const YEAR = 'year';

    const ACTIVE = 1;
    const INACTIVE = 2;

    protected $guarded = ['id'];
    protected $fillable = ['name', 'currency', 'description', 'price', 'user_id', 'number_appointments', 'period', 'image_background', 'state', 'time_interval_appointments'];

    public function subscription(){
        $this->hasOne(Subscription::class, 'plan_id');
    }
}
