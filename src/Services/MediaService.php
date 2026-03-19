<?php

namespace Waad\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Waad\Media\Dto\MediaDto;
use Waad\Media\Media;

class MediaService
{
    private $model;

    private Collection|array|UploadedFile|Media|int|string|null $files;

    private $index;

    private ?string $label = null;

    private ?string $collection;

    private ?string $disk;

    private ?string $bucket;

    private ?array $registerMediaCollections = null;

    private Collection $result;

    public function __construct($model, $files = null)
    {
        $this->model = $model;
        $this->files = $files;
        $this->result = collect();

        $this->collection = config('media.default_collection');
        $this->disk = config('media.disk');
        $this->bucket = config('media.bucket');
        $this->registerMediaCollections = $model->registerCollections();

        if (property_exists($this->model, 'media_disk')) {
            $this->disk = $this->model->media_disk ?? $this->disk;
        }

        if (property_exists($this->model, 'media_bucket')) {
            $this->bucket = $this->model->media_bucket ?? $this->bucket;
        }
    }

    protected function isList(): bool
    {
        return is_array($this->files) ||
               ($this->files instanceof \ArrayAccess && $this->files instanceof \Traversable) ||
               $this->files instanceof Collection;
    }

    protected function getModel()
    {
        return $this->model;
    }

    protected function setModel($model): self
    {
        $this->model = $model;

        return $this;
    }

    protected function setFiles($files): self
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return Collection<UploadedFile>|array<UploadedFile>|array<int>|array<string>|array<Media>|UploadedFile|Media|int|string|null
     */
    protected function getFiles()
    {
        return $this->files;
    }

    protected function setIndex(int $index): self
    {
        $this->index = $index;

        return $this;
    }

    protected function getIndex(): int
    {
        return $this->index ?? 1;
    }

    protected function getLabel(): ?string
    {
        return $this->label;
    }

    protected function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    protected function getCollection(): ?string
    {
        return $this->collection;
    }

    protected function setCollection(?string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    protected function getDisk(): ?string
    {
        return $this->disk;
    }

    protected function setDisk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    protected function getBucket(): ?string
    {
        return $this->bucket;
    }

    protected function setBucket(string $bucket): self
    {
        $this->bucket = $bucket;

        return $this;
    }

    protected function getRegisterMediaCollections(): ?array
    {
        return $this->registerMediaCollections;
    }

    protected function setRegisterMediaCollections(?array $registerMediaCollections): self
    {
        $this->registerMediaCollections = $registerMediaCollections;

        return $this;
    }

    protected function getResult(): Collection
    {
        return $this->result;
    }

    protected function setResult(Media|bool $result): self
    {
        $this->result->push($result);

        return $this;
    }

    /**
     * Set data from DTO to array
     */
    protected function setData(MediaDto $fileDto, bool $set_user = true): array
    {
        $user = auth()->user();

        $data = [
            'basename' => $fileDto->basename,
            'filename' => $fileDto->filename,
            'path' => $fileDto->path,
            'index' => $fileDto->index,
            'label' => $fileDto->label,
            'collection' => $fileDto->collection,
            'disk' => $fileDto->disk,
            'bucket' => $fileDto->bucket,
            'mimetype' => $fileDto->mimeType,
            'filesize' => $fileDto->fileSize,
            'approved' => config('media.default_approved', true),
            'metadata' => $fileDto->metadata ?? [],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($set_user && $user) {
            $data['user_id'] = $user->id;
            $data['user_type'] = get_class($user);
        } else {
            $data['user_id'] = null;
            $data['user_type'] = null;
        }

        return $data;
    }
}
