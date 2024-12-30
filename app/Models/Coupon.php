<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    const ACTIVE = 1;
    const INACTIVE = 2;

    protected $fillable = ['name', 'description','limit_use', 'discount', 'create_user_id', 'date_expiration', 'limit_use_per_user', 'limit_use_per_coupon', 'except_plans'];
}
