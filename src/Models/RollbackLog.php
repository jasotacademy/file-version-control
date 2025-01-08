<?php

namespace Jasotacademy\FileVersionControl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RollbackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_version_id',
        'rolled_back_by',
        'rolled_back_at',
        'note',
    ];

    public function fileVersion(): BelongsTo
    {
        return $this->belongsTo(FileVersion::class);
    }

    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        return $this->belongsTo($userModel, 'rolled_back_by');
    }
}