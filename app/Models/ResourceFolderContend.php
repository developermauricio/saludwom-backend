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

    public function categories(){
        return $this->belongsToMany(Category::class, 'category_content_resource', 'resource_folder_contend_id', 'category_id');
    }

    public function archive()
    {
        return $this->belongsTo(Archive::class, 'archive_id');
    }

}
