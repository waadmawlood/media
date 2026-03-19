<?php

namespace Waad\Media;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Waad\Media\Helpers\FileSystem;

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
     * Get the public full URL for accessing the media file
     */
    public function getFullUrlAttribute(): ?string
    {
        if (! config('media.enable_full_url', true)) {
            return null;
        }

        $disk = $this->disk ?? config('media.disk');
        $bucket = $this->bucket ?? config('media.bucket');
        $shortcut = config("media.shortcut.{$disk}");

        return $this->basename ? sprintf('%s/%s', url("{$shortcut}/{$bucket}/"), $this->basename) : null;
    }

    /**
     * Get the private temporary URL for accessing the media file (S3 only)
     */
    public function getTemporaryUrlAttribute(): ?string
    {
        if (! config('media.enable_temporary_url', true)) {
            return null;
        }

        $disk = $this->disk ?? config('media.disk');

        if (! FileSystem::isDiskS3($disk) || ! $this->path) {
            return null;
        }

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

        return $format ? $date->format($format) : $date->format('Y-m-d H:i:s');
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
