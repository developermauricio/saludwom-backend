<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    const ACTIVE = 1;
    const INACTIVE = 2;

    use HasFactory;
    protected $fillable = ['user_id', 'path_file', 'name_file', 'archiveable_type', 'archiveable_id', 'type_file', 'storage'];

    public function resourcesFolderContent(){
        return $this->hasOne(ResourceFolderContend::class, 'archive_id');
    }
}
