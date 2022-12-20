<?php

namespace Waad\Media\Traits;

use Waad\Media\Helpers\FileMedia;

trait HasMedia
{
    /**
     * function to add media for model.
     *
     * @param mixed|array $files
     * @param int|array   $index
     * @return array
     */
    public function addMedia($files, $index = 1, $label = null)
    {
        $mediaObject = new FileMedia($this);
        $mediaObject->uploading($files, $index, $label);

        return $mediaObject->storeMedia();
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
        $mediaObject = new FileMedia($this);
        $mediaObject->delete($ids);
    }
}
