<?php

namespace Waad\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Waad\Media\Contracts\MediaService as MediaServiceInterface;
use Waad\Media\Dto\MediaDto;
use Waad\Media\Helpers\Files;
use Waad\Media\Media;

class MediaUploadService extends MediaService implements MediaServiceInterface
{
    private bool $isList;

    public function __construct(protected $model, protected $files = null)
    {
        parent::__construct($model, $files);
    }

    public function index(int $index): static
    {
        return $this->setIndex($index);
    }

    public function label(string $label): static
    {
        return $this->setLabel($label);
    }

    public function collection(string $collection): static
    {
        $this->selectCollection($collection);

        return $this;
    }

    public function disk(string $disk): static
    {
        return $this->setDisk($disk);
    }

    public function bucket(string $bucket): static
    {
        return $this->setBucket($bucket);
    }

    /**
     * Upload files to the configured disk (local, s3, or any Laravel disk).
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
            Log::error('Media upload failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function sync(?string $collection = null, ?string $disk = null): Media|Collection|null
    {
        return $this->upload($collection, $disk);
    }

    /**
     * Check if a file exists on the current disk.
     */
    public function fileExists(string $path): bool
    {
        try {
            return Storage::disk($this->getDisk())->exists($path);
        } catch (\Exception $e) {
            Log::error('Media file exists check failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Delete a file from the current disk.
     */
    public function deleteFile(string $path): bool
    {
        try {
            return Storage::disk($this->getDisk())->delete($path);
        } catch (\Exception $e) {
            Log::error('Media file delete failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get file size from the current disk.
     */
    public function fileSize(string $path): ?int
    {
        try {
            return Storage::disk($this->getDisk())->size($path);
        } catch (\Exception $e) {
            Log::error('Media file size check failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get file metadata from the current disk.
     */
    public function fileMetadata(string $path): ?array
    {
        try {
            $disk = Storage::disk($this->getDisk());

            return [
                'size' => $disk->size($path),
                'mimetype' => $disk->mimeType($path),
                'last_modified' => $disk->lastModified($path),
            ];
        } catch (\Exception $e) {
            Log::error('Media metadata retrieval failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get a temporary URL for a file (supported on S3 and compatible disks).
     */
    public function temporaryUrl(string $path, int $minutes = 5): ?string
    {
        try {
            return Storage::disk($this->getDisk())
                ->temporaryUrl($path, now()->addMinutes($minutes));
        } catch (\Exception $e) {
            Log::error('Media temporary URL generation failed: '.$e->getMessage());

            return null;
        }
    }

    protected function uploadFiles(): Collection|Media|null
    {
        $this->deleteOldMediaIfSingleCollection();

        return $this->isList
            ? $this->uploadManyFiles()
            : $this->uploadOneFile();
    }

    private function deleteOldMediaIfSingleCollection(): void
    {
        $registers = $this->getRegisterMediaCollections();
        $collection = $this->getCollection();

        if (blank($registers) || blank($collection)) {
            return;
        }

        $register = $registers[$collection] ?? null;
        $isSingle = $register['single'] ?? false;

        if (blank($register) || ! $isSingle) {
            return;
        }

        $this->getModel()->media()
            ->where('collection', $collection)
            ->delete();
    }

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
