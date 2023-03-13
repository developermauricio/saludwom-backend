<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function notificationCount()
    {
        return $this->notifications()->count();
    }
}
