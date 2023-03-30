<?php

namespace Waad\Media\Services;

use Waad\Media\Media;
use Waad\Media\DTO\FileDTO;
use Waad\Media\Helpers\Files;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class MediaUploadingService extends MediaService
{

    private bool $isList;
    public function __construct($model, $files = null)
    {
        parent::__construct($model, $files);
    }

    /**
     * set Index
     *
     * @param int $index
     * @return MediaUploadingService
     */
    public function index(int $index)
    {
        return $this->setIndex($index);
    }

    /**
     * set Label
     *
     * @param string $label
     * @return MediaUploadingService
     */
    public function label(string $label)
    {
        return $this->setLabel($label);
    }

    /**
     * set Disk
     *
     * @param string $disk
     * @return MediaUploadingService
     */
    public function disk(string $disk)
    {
        return $this->setDisk($disk);
    }

    /**
     * set Directory
     *
     * @param string $directory
     * @return MediaUploadingService
     */
    public function directory(string $directory)
    {
        return $this->setDirectory($directory);
    }

    /**
     * uploading
     *
     * @return Media|array<Media>|null
     */
    public function upload()
    {
        if (blank($this->getFiles()))
            return null;

        $this->isList = $this->isList();

        return $this->uploadFiles();
    }

    /**
     * sync
     *
     * @return Media|array<Media>|null
     */
    public function sync()
    {
        return $this->upload();
    }

    /**
     * Upload Files
     *
     * @return Collection|Media|null
     */
    protected function uploadFiles()
    {
        return $this->isList ?
            $this->uploadManyFiles() :
            $this->uploadOneFile();
    }

    /**
     * Upload One File
     *
     * @param UploadedFile|null $file
     * @return Media|null
     */
    private function uploadOneFile(UploadedFile|null $file = null)
    {
        $fileDto = Files::uploadFile($file ?? $this->getFiles(), $this->getDirectory(), $this->getDisk());
        if (blank($fileDto))
            return null;

        $fileDto->disk = $this->getDisk();
        $fileDto->directory = $this->getDirectory();
        $fileDto->label = $this->getLabel();
        $fileDto->index = $this->getIndex();

        $this->isList ? $this->setIndex($this->getIndex() + 1) : null;

        return $this->saveMedia($fileDto);
    }

    /**
     * Upload Many Files
     *
     * @return Collection
     */
    private function uploadManyFiles()
    {
        foreach ($this->getFiles() as $file) {
            $media = $this->uploadOneFile($file);

            if (filled($media))
                $this->setResult($media);
        }

        return $this->getResult();
    }

    /**
     * Save Media
     *
     * @param FileDTO $fileDto
     * @return Media
     */
    private function saveMedia(FileDTO $fileDto)
    {
        return $this->getModel()->media()->create($this->setData($fileDto));
    }
}
