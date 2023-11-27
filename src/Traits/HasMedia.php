<?php

namespace Waad\Media\Traits;

use Waad\Media\Helpers\FileMedia;
use Waad\Media\Media;

trait HasMedia
{
        /**
     * function to add media for model.
     *
     * @param \Illuminate\Http\UploadedFile|array|null $files
     * @param int|array   $index
     * @param string|null $label
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
     * @param \Illuminate\Http\UploadedFile|array|null $files
     * @param int|array   $index
     * @param string|null $label
     * @return array|null
     */
    public function syncMedia($file, $index = 1, $label = null)
    {
        if (!$file) {
            return null;
        }

        $this->deleteMedia();

        return $this->addMedia($file, $index, $label);
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

    /**
     * total Files Size
     *
     * @return int
     */
    public function mediaTotalSize()
    {
        return $this->media()->sum('file_size');
    }

    /**
     * total Count Media
     *
     * @return int
     */
    public function mediaTotalCount()
    {
        return $this->media()->count();
    }

    /**
     * Get Media By Id
     *
     * @param int $id
     * @return Media|null
     */
    public function mediaById(int $id)
    {
        return $this->media()->find($id);
    }


    /**
     * Get Media By Mime Type
     *
     * @param string $mimeType
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function mediaByMimeType(string $mimeType)
    {
        return $this->media()->where('mime_type', $mimeType)->get();
    }

    /**
     * Get Media By Approved
     *
     * @param bool $approved
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function mediaApproved(bool $approved = true)
    {
        return $this->media()->where('approved', $approved)->get();
    }
}
