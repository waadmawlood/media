<?php

namespace Waad\Media\Traits;

trait HasManyMedia
{
    use HasMedia;

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
