<?php

namespace Jasotacademy\FileVersionControl\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jasotacademy\FileVersionControl\Database\Factories\FileVersionFactory;

class FileVersion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_versions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_id',
        'version_number',
        'path',
        'filename',
        'mime_type',
        'size',
        'metadata',
        'created_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function newFactory(): FileVersionFactory
    {
        return FileVersionFactory::new();
    }

    /**
     * Soft delete this version fluently.
     *
     * @return $this
     */
    public function softDelete(): static
    {
        $this->delete();

        return $this;
    }

    /**
     * Restore this version fluently.
     *
     * @return $this
     */
    public function restoreVersion(): static
    {
        $this->restore();

        return $this;
    }

    /**
     * Permanently delete this version fluently.
     *
     * @return $this
     */
    public function forceDeleteVersion(): static
    {
        $this->forceDelete();

        return $this;
    }

    /**
     * Scope to include soft-deleted records.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithSoftDeleted(Builder $query): Builder
    {
        return $query->withTrashed();
    }

    /**
     * Scope to only include soft-deleted records.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlySoftDeleted(Builder $query): Builder
    {
        return $query->onlyTrashed();
    }
}