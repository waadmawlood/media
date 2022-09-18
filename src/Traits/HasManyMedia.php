<?php

namespace Waad\Media\Traits;

trait HasManyMedia
{
    use HasMedia;

    /**
     * Order media table asc , desc and detrmaine the column
     * There is in config/media.php => order array
     */
    public $orderMedia = $this->getFromConfig('media.order');

    /**
     * Return all media for this model.
     *
     * @return MorphMany
     */
    public function media()
    {
        return $this->morphMany(config('media.media_class'), 'model')->orderBy($this->orderMedia['column'], $this->orderMedia['type']);
    }
}
