<?php

namespace Waad\Media;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'basename',
        'filename',
        'path',
        'index',
        'label',
        'collection',
        'disk',
        'bucket',
        'mimetype',
        'filesize',
        'approved',
        'metadata',
    ];

    protected $appends = [
        'full_url',
        'temporary_url',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'metadata' => 'json',
    ];

    protected $hidden = [
        'disk',
        'bucket',
    ];

    public function getTable()
    {
        return $this->table ?? config('media.table_name');
    }

    /**
     * Get the public full URL for accessing the media file.
     * Uses Storage::url() for non-local disks (S3, etc.)
     * and shortcut-based URL construction for local disks.
     */
    public function getFullUrlAttribute(): ?string
    {
        if (! config('media.enable_full_url', true) || ! $this->path) {
            return null;
        }

        $disk = $this->disk ?? config('media.disk');

        try {
            return $this->basename ? Storage::disk($disk)->url($this->path) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get a temporary URL for accessing the media file.
     * Works with any disk that supports temporary URLs (S3, etc.).
     */
    public function getTemporaryUrlAttribute(): ?string
    {
        if (! config('media.enable_temporary_url', true)) {
            return null;
        }

        $disk = $this->disk ?? config('media.disk');

        try {
            $ttl = config('media.s3.default_ttl_temporary_url', 5);

            return Storage::disk($disk)->temporaryUrl($this->path, now()->addMinutes($ttl));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Format datetime for serialization
     */
    public function serializeDate(DateTimeInterface $date): string
    {
        $format = config('media.format_date');

        return $format ? $date->format($format) : parent::serializeDate($date);
    }

    /**
     * Scope query to approved media only
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Get the parent model that owns the media
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo('mediable');
    }

    /**
     * Get the user that owns the media
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark media as approved
     */
    public function approve(): self
    {
        $this->update(['approved' => true]);

        return $this;
    }

    /**
     * Mark media as not approved
     */
    public function disApprove(): self
    {
        $this->update(['approved' => false]);

        return $this;
    }
}
