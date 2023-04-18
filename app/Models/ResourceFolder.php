<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceFolder extends Model
{
    use HasFactory;

    const ACTIVE = 1;
    const INACTIVE = 2;

    protected $guarded = 'id';
    protected $fillable = ['id','folder', 'description', 'state'];

    public function archives()
    {
        return $this->morphMany(Archive::class, 'archiveable');
    }

    public function archive()
    {
        $this->archives()->firstOrCreate([
            'user_id' => auth()->id()
        ]);
    }

    public function archiveCount()
    {
        return $this->archives()->count() ?? '';
    }
}
