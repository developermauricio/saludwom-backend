<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessages extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['message', 'type', 'chat_channel_id', 'send_user_id', 'recipient_user_id', 'read_at'];
}
