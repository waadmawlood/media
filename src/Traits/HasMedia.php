<?php

namespace Waad\Media\Traits;

use Waad\Media\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Waad\Media\Services\MediaDeletingService;
use Waad\Media\Services\MediaUploadingService;

trait HasMedia
{
    /**
     * function to add media for model.
     *
     * @param UploadedFile|array<UploadedFile>|null $files
     * @return MediaUploadingService
     */
    public function addMedia(UploadedFile|array|null $files)
    {
        $service = new MediaUploadingService($this);
        $service->setFiles($files);

        return $service;
    }

    /**
     * function to update media of model
     *
     * @param mixed UploadedFile|array<UploadedFile>|null $files
     * @param array $ids
     * @return MediaUploadingService
     */
    public function syncMedia(UploadedFile|array|null $files = null, array $ids = [])
    {
        $this->deleteMedia($ids)->delete();

        return $this->addMedia($files);
    }

    /**
     * function to delete media of model
     *
     * @param mixed $files
     * @return MediaDeletingService
     */
    public function deleteMedia($files = null)
    {
        $service = new MediaDeletingService($this);

        if (filled($files)) {
            $service->setFiles($files);
        } else {
            $service->setFiles($this->media);
        }

        return $service;
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
     * @param bool $withTrashed
     * @return int
     */
    public function mediaTotalCount(bool $withTrashed = false)
    {
        $media = $this->media();

        if($withTrashed)
            $media = $media->withTrashed();

        return $media->count();
    }

    /**
     * Get Media By Id
     *
     * @param int $id
     * @param bool $withTrashed
     * @return Media|null
     */
    public function mediaById(int $id, bool $withTrashed = false)
    {
        $media = $this->media();

        if($withTrashed)
            $media = $media->withTrashed();

        return $media->find($id);
    }


    /**
     * Get Media By Mime Type
     *
     * @param string $mimeType
     * @param bool $withTrashed
     * @return Collection|null
     */
    public function mediaByMimeType(string $mimeType, bool $withTrashed = false)
    {
        $media = $this->media()->where('mime_type', $mimeType);

        if($withTrashed)
            $media = $media->withTrashed();

        return $media->get();
    }

    /**
     * Get Media By Approved
     *
     * @param bool $approved
     * @param bool $withTrashed
     * @return Collection|null
     */
    public function mediaApproved(bool $approved = true, bool $withTrashed = false)
    {
        $media = $this->media()->where('approved', $approved);

        if($withTrashed)
            $media = $media->withTrashed();

        return $media->get();
    }
}
