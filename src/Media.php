<?php

namespace Waad\Media;

use Exception;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\MassPrunable;

class Media extends Model
{
    use SoftDeletes;
    use MassPrunable;

    /**
     * fillable columns can insert and updated from user
     * @var array
     */
    protected $fillable = [
        'base_name',
        'file_name',
        'path',
        'index',
        'label',
        'disk',
        'directory',
        'mime_type',
        'file_size',
        'approved',
        'user_id',
        'user_type',
    ];

    /**
     * @var array
     */
    protected $appends = ['url'];

    /**
     * @var array
     */
    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'disk',
        'directory',
    ];

    /**
     * Accessor to append path file from shortcut.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        $disk = $this->attributes['disk'] ?? $this->getFromConfig('media.disk');
        $directory = $this->attributes['directory'] ?? $this->getFromConfig('media.directory');
        $shortcut = $this->getFromConfig("media.shortcut.{$disk}");

        return sprintf('%s/%s', url("{$shortcut}/{$directory}/"), $this->attributes['base_name']);
    }

    /**
     * Format data time of columns that it's type is timestamp
     * There is in config/media.php => format_date.
     */
    public function serializeDate(DateTimeInterface $date)
    {
        $format = $this->getFromConfig('media.format_date');

        return $format ? $date->format($this->getFromConfig('media.format_date')) : $date;
    }

    /**
     * scope to return only records that it's approved => true
     * use it e.g. $post->media->approved();.
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * morph relationship with any table can use media with it
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo('model');
    }

    /**
     * morph User OR Any Model Guard Authorization
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo();
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

    /**
     * return from config
     *
     * @param string $value
     * @return mixed
     */
    private function getFromConfig(string $value)
    {
        return config($value);
    }

    /**
     * prunable
     * @throws Exception
     * @return Media
     */
    public function prunable()
    {
        $days = $this->getFromConfig('media.delete_file_after_day');

        if (!is_int($days)) {
            throw new Exception("media delete after day in config/media.php is not integer");
        }

        return static::where('created_at', '<', now()->subDays($days));
    }
}
