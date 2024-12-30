<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Subscription extends Model
{
    use HasFactory;

    const PENDING = 1;
    const CANCELLED = 2;
    const REJECTED = 3;
    const ACCEPTED = 4;
    const COMPLETED = 5;

    protected $guarded = ['id'];
    protected $fillable = ['plan_id', 'patient_id', 'expiration_date', 'state', 'name'];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'subscription_id');
    }

    static public function validatePeriod($period)
    {
        $date = null;
        if ($period) {
            switch ($period) {
                case 'week':
                    $date = Carbon::now()->addWeeks(1);
                    break;
                case 'month':
                    $date = Carbon::now()->addMonth();
                    break;
                case 'year':
                    $date = Carbon::now()->addYear();
                    break;
            }
            return $date->format('Y-m-d H:i:s');
        }
    }
}
