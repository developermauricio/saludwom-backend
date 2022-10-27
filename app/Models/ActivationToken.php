<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivationToken extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'token'];
    protected  $primaryKey = 'token';
    protected $dates = ['created_at'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    static public function activationToken($user)
    {
        $activatedToken = ActivationToken::create([
            'user_id' => $user->id,
            'token' => Str::random(60)
        ]);

        return $activatedToken;
    }
}
