<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentValuation extends Model
{
    use HasFactory;
    const PENDING = 1;
    const CANCELLED = 2;
    const FINISHED = 3;
}
