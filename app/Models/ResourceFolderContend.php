<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceFolderContend extends Model
{
    use HasFactory;
    const ACTIVE = 1;
    const INACTIVE = 2;

    protected $guarded = 'id';
    protected $fillable = ['id','name', 'description','archive_id', 'state'];

    public function treatments(){
        return $this->belongsToMany(TypeTreatment::class, 'r_folder_contents_treatmets', 'resource_folder_contend_id', 'type_treatment_id');
    }
}
