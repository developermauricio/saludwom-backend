<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeTreatment extends Model
{
    use HasFactory;
    const ACTIVE = 1;
    const INACTIVE = 2;
}
