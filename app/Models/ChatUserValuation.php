<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatUserValuation extends Model
{
    use HasFactory;

    const ONLINE = true;
    const OFFLINE = false;

    protected $guarded = 'id';
    protected $fillable = ['chat_channel_id', 'user_id', 'online', 'receive_notification'];
}
