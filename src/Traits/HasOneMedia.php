<?php

namespace Waad\Media\Traits;

trait HasOneMedia
{
    use HasMedia;

    /**
     * Return last media for this model.
     *
     * @return MorphOne
     */
    public function media()
    {
        return $this->morphOne(config('media.media_class'), 'model')->latest();
    }
}
