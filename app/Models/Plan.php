<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    const WEEK = 1;
    const MONTH = 2;
    const YEAR = 3;

    const ACTIVE = 1;
    const INACTIVE = 2;
}
