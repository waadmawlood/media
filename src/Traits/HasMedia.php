<?php

namespace Waad\Media\Traits;

use Waad\Media\Helpers\AddMedia;
use Waad\Media\Helpers\DeleteMedia;

trait HasMedia
{
    /**
     * function to add media for model.
     *
     * @param mixed|array $files
     * @param int|array   $index
     * @return array
     */
    public function addMedia($files, $index = 1)
    {
        $mediaObject = new AddMedia($files, $this);

        return $mediaObject->addFileToMedia();
    }

    /**
     * function to update media of model
     *
     * @param mixed|array $files
     * @param int|array   $index
     * @return array|null
     */
    public function syncMedia($file, $index = 1)
    {
        if (!$file) {
            return null;
        }

        $this->deleteMedia();

        return $this->addMedia($file, $index);
    }

    /**
     * function to delete media of model
     *
     * @param null|array $ids
     * @return void|null
     */
    public function deleteMedia($ids = null)
    {
        new DeleteMedia($ids, $this);
    }
}
