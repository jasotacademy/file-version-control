<?php

namespace Jasotacademy\FileVersionControl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    protected $fillable = [
        'name',         
        'path',         
        'mime_type',    
        'size',        
    ];
    
    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class);
    }

    public function latestVersions()
    {
        return $this->versions()->orderBy('created_at', 'desc')->first();
    }
}
