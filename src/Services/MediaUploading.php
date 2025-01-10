<?php

namespace Waad\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Waad\Media\Dto\MediaDto;
use Waad\Media\Helpers\Files;
use Waad\Media\Media;

class MediaUploading extends MediaService
{
    private bool $isList;

    private ?Collection $result;

    public function __construct(protected $model, protected $files = null)
    {
        parent::__construct($model, $files);
        $this->result = collect();
    }

    /**
     * set Index
     */
    public function index(int $index): static
    {
        return $this->setIndex($index);
    }

    /**
     * set Label
     */
    public function label(string $label): static
    {
        return $this->setLabel($label);
    }

    /**
     * set Collection
     */
    public function collection(string $collection): static
    {
        $this->selectCollection($collection);

        return $this;
    }

    /**
     * set Disk
     */
    public function disk(string $disk): static
    {
        return $this->setDisk($disk);
    }

    /**
     * set bucket
     */
    public function bucket(string $bucket): static
    {
        return $this->setbucket($bucket);
    }

    /**
     * uploading
     */
    public function upload(?string $collection = null, ?string $disk = null): Media|Collection|null
    {
        if (filled($collection)) {
            $this->selectCollection($collection);
        }

        if (filled($disk)) {
            $this->setDisk($disk);
        }

        if (blank($this->getFiles())) {
            return null;
        }

        $this->isList = $this->isList();

        try {
            return $this->uploadFiles();
        } catch (\Exception $e) {
            \Log::error('Media upload failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * sync
     */
    public function sync(?string $collection = null, ?string $disk = null): Media|Collection|null
    {
        return $this->upload($collection, $disk);
    }

    /**
     * Upload Files
     */
    protected function uploadFiles(): Collection|Media|null
    {
        return $this->isList
            ? $this->uploadManyFiles()
            : $this->uploadOneFile();
    }

    /**
     * Upload One File
     */
    private function uploadOneFile(?UploadedFile $file = null): ?Media
    {
        $fileDto = Files::uploadFile($file ?? $this->getFiles(), $this->getBucket(), $this->getDisk());

        if (blank($fileDto)) {
            return null;
        }

        $fileDto->disk = $this->getDisk();
        $fileDto->bucket = $this->getBucket();
        $fileDto->label = $this->getLabel();
        $fileDto->collection = $this->getCollection();
        $fileDto->index = $this->getIndex();

        if ($this->isList) {
            $this->setIndex($this->getIndex() + 1);
        }

        return $this->saveMedia($fileDto);
    }

    /**
     * Upload Many Files
     */
    private function uploadManyFiles(): Collection
    {
        $files = collect($this->getFiles());

        return $files->map(function ($file) {
            $media = $this->uploadOneFile($file);
            if (filled($media)) {
                $this->setResult($media);
            }

            return $media;
        })->filter();
    }

    /**
     * Save Media
     */
    private function saveMedia(MediaDto $fileDto): Media
    {
        return $this->getModel()->media()->create($this->setData($fileDto));
    }

    private function selectCollection(string $collection): void
    {
        $registers = $this->getRegisterMediaCollections();
        if (empty($registers)) {
            return;
        }

        $register = $registers[$collection] ?? null;
        if (empty($register)) {
            return;
        }

        $this->setCollection($collection);
        $this->disk($register['disk'] ?? $this->getDisk());
        $this->bucket($register['bucket'] ?? $this->getBucket());
        $this->label($register['label'] ?? $this->getLabel());
    }
}
