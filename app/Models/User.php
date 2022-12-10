<?php

namespace App\Models;



use App\Notifications\PasswordReset;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Billable;

    const ACTIVE = 1;
    const INACTIVE = 2;
    const PENDING_ACCOUNT_ACTIVATION = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'state',
        'slug',
        'email',
        'phone',
        'picture',
        'city_id',
        'password',
        'last_name',
        'last_login',
        'country_id',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token){
        $this->notify(new PasswordReset($token));
    }

    public function patient(){
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function identificationType(){
        return $this->belongsTo(IdentificationType::class, 'identification_type_id');
    }

}
