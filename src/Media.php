<?php

namespace Waad\Media;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('media.table_name'));

        if (config('media.uuid', false)) {
            $this->usesUniqueIds = true;
        }
    }

    public function uniqueIds()
    {
        return config('media.uuid', false) ? [$this->getKeyName()] : [];
    }

    public function newUniqueId()
    {
        return config('media.uuid', false) ? (string) Str::orderedUuid() : null;
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (config('media.uuid', false)) {
            if ($field && in_array($field, $this->uniqueIds()) && ! Str::isUuid($value)) {
                throw (new ModelNotFoundException)->setModel(get_class($this), $value);
            }

            if (! $field && in_array($this->getRouteKeyName(), $this->uniqueIds()) && ! Str::isUuid($value)) {
                throw (new ModelNotFoundException)->setModel(get_class($this), $value);
            }
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    public function getKeyType()
    {
        if (config('media.uuid', false) && in_array($this->getKeyName(), $this->uniqueIds())) {
            return 'string';
        }

        return $this->keyType;
    }

    public function getIncrementing()
    {
        if (config('media.uuid', false) && in_array($this->getKeyName(), $this->uniqueIds())) {
            return false;
        }

        return $this->incrementing;
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
     * Get the private temporary URL for accessing the media file
     */
    public function getTemporaryUrlAttribute(): ?string
    {
        if (! config('media.enable_temporary_url', true)) {
            return null;
        }

        $disk = $this->disk ?? config('media.disk');
        $bucket = $this->bucket ?? config('media.bucket');
        $shortcut = config("media.shortcut.{$disk}");

        return $this->basename ? sprintf('%s/%s', url("{$shortcut}/{$bucket}/"), $this->basename) : null;
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
    public function mediable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('mediable');
    }

    /**
     * Get the user that owns the media
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\MorphTo
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
