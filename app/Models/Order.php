<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    const PENDING = 1;
    const CANCELLED = 2;
    const REJECTED = 3;
    const ACCEPTED = 4;

    protected $guarded = ['id'];
    protected $fillable = ['plan_id', 'patient_id', 'price_total', 'invoice_id', 'coupon', 'state'];

    public function plan(){
        return $this->belongsTo(Plan::class, 'plan_id');
    }

}
