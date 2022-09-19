<?php

namespace Waad\Media;

use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    // fillable columns can insert and updated from user
    protected $fillable = [
        'base_name',
        'file_name',
        'index',
        'mime_type',
        'file_size',
        'approved',
        'user_id',
    ];

    protected $appends = ['path'];

    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * Accessor to append path file from shortcut.
     */
    public function getPathAttribute()
    {
        return sprintf('%s/%s/%s', $this->getFromConfig('app.url'), $this->getFromConfig('media.shortcut'), $this->attributes['base_name']);
    }

    /**
     * Format data time of columns that it's type is timestamp
     * There is in config/media.php => format_date.
     */
    public function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->getFromConfig('media.format_date'));
    }

    /**
     * scope to return only records that it's approved => true
     * use it e.g. $post->media->approved();.
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    // morph relationship with any table can use media with it
    public function model()
    {
        return $this->morphTo('model');
    }

    // Has one user relationship with record media
    public function user()
    {
        return $this->belongsTo($this->getAuthModelName(), 'user_id');
    }

    /**
     * Update all media of object approved = true
     * use it e.g. $post->media->approve();.
     */
    public function approve()
    {
        $this->update([
            'approved' => true,
        ]);

        return $this;
    }

    /**
     * Update all media of object approved = false
     * use it e.g. $post->media->disApprove();.
     */
    public function disApprove()
    {
        $this->update([
            'approved' => false,
        ]);

        return $this;
    }

    // Return user model there is in config/media.php => user_model
    // can use any guard table in media
    private function getAuthModelName()
    {
        if (config('media.user_model')) {
            return config('media.user_model');
        }

        if (!is_null(config('auth.providers.users.model'))) {
            return config('auth.providers.users.model');
        }

        throw new Exception('Could not determine the user model name.');
    }

    // return from config
    private function getFromConfig($value)
    {
        return (string) config($value);
    }
}
