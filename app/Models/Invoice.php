<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['patient_id', 'plan_id', 'order_id', 'invoice_stripe_id'];
}
