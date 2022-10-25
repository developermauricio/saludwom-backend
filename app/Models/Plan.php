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
}
