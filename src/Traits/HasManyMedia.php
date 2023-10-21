<?php

namespace Waad\Media\Traits;

use Waad\Media\Media;

trait HasManyMedia
{
    use HasMedia;

    /**
     * Order media table asc , desc and detrmaine the column
     * There is in config/media.php => order array
     *
     * Return all media for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Query\Builder
     */
    public function media()
    {
        return $this->morphMany(config('media.model', Media::class), 'model')->orderBy(config('media.order.column'), config('media.order.type'));
    }
}
