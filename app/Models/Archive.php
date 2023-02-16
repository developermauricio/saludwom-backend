<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'path_file', 'name_file', 'archiveable_type', 'archiveable_id', 'type_file', 'storage'];
}
