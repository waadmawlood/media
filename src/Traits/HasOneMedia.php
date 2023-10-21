<?php

namespace Waad\Media\Traits;

use Waad\Media\Media;

trait HasOneMedia
{
    use HasMedia;

    /**
     * Return last media for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function media()
    {
        return $this->morphOne(config('media.model', Media::class), 'model')->latest();
    }
}
