<?php

namespace Waad\Media;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Waad\Media\Services\MediaDeletingService;
use Waad\Media\Services\MediaUploadService;

trait HasMedia
{
    public ?array $registerMediaCollections = null;

    public bool $useIndexMedia = true;

    /**
     * Enable ordering media by index column.
     */
    public function orderByIndexMedia(bool $enabled = true): static
    {
        $this->useIndexMedia = $enabled;

        return $this;
    }

    /**
     * Apply index ordering to a query if useIndex is enabled.
     */
    private function applyIndexOrder($query)
    {
        return $this->useIndexMedia ? $query->orderBy('index') : $query;
    }

    /**
     * Add media for model.
     *
     * @param  UploadedFile|array<UploadedFile>|null  $files
     */
    public function addMedia(UploadedFile|array|null $files): MediaUploadService
    {
        return new MediaUploadService($this, $files);
    }

    /**
     * Update media of model by syncing files and deleting specified IDs.
     *
     * IDs are removed when {@see MediaUploadService::upload()} runs, scoped to the collection
     * set with {@see MediaUploadService::collection()} or the {@see MediaUploadService::upload()} argument
     * (e.g. only rows in that collection are deleted — other collections are untouched).
     *
     * @param  UploadedFile|array<UploadedFile>|null  $files
     * @param  array<int>|Collection<int, int>  $ids  Media IDs to remove before upload; empty means do not delete by ID
     */
    public function syncMedia(UploadedFile|array|null $files = null, array|Collection $ids = []): MediaUploadService
    {
        $idsToDelete = $ids instanceof Collection ? $ids->all() : $ids;
        $upload = $this->addMedia($files);
        $upload->setIsSyncMethod(true);

        if ($idsToDelete !== []) {
            $upload->withSyncDeleteIds($idsToDelete);
        }

        return $upload;
    }

    public function syncMediaWithoutDettached(UploadedFile|array|null $files = null): MediaUploadService
    {
        return $this->addMedia($files)->setIsWithDettachedSync(false);
    }

    /**
     * Delete media of model.
     *
     * @param  mixed  $files
     */
    public function deleteMedia($files = null): MediaDeletingService
    {
        if (($files instanceof Collection && $files->isEmpty()) || (is_array($files) && $files === [])) {
            return new MediaDeletingService($this, []);
        }

        return new MediaDeletingService($this, filled($files) ? $files : $this->media);
    }

    /**
     * Get total files size.
     */
    public function mediaTotalSize(bool $withTrashed = false): int
    {
        return $this->media()
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->sum('filesize');
    }

    /**
     * Get total files size by collection.
     */
    public function mediaTotalSizeByCollection(?string $collection = null, bool $withTrashed = false): int
    {
        $collection = $collection ?? config('media.default_collection');
        if (blank($collection)) {
            return 0;
        }

        return $this->media()
            ->where('collection', $collection)
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->sum('filesize') ?? 0;
    }

    /**
     * Get total media count.
     *
     * @param  bool  $withTrashed  Include soft deleted media
     */
    public function mediaTotalCount(bool $withTrashed = false): int
    {
        return $this->media()
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->count();
    }

    /**
     * Get total media count by collection.
     *
     * @param  bool  $withTrashed  Include soft deleted media
     */
    public function mediaTotalCountByCollection(?string $collection = null, bool $withTrashed = false): int
    {
        $collection = $collection ?? config('media.default_collection');
        if (blank($collection)) {
            return 0;
        }

        return $this->media()
            ->where('collection', $collection)
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->count();
    }

    /**
     * Get media by ID.
     *
     * @param  bool  $withTrashed  Include soft deleted media
     */
    public function mediaById(int|string $id, bool $withTrashed = false): ?Media
    {
        return $this->media()
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->find($id);
    }

    /**
     * Get media by mime type.
     *
     * @param  bool  $withTrashed  Include soft deleted media
     */
    public function mediaByMimeType(string $mimeType, bool $withTrashed = false): Collection
    {
        return $this->applyIndexOrder(
            $this->media()
                ->where('mimetype', $mimeType)
                ->when($withTrashed, fn ($q) => $q->withTrashed())
        )->get();
    }

    /**
     * Get media by mime type by collection.
     */
    public function mediaByMimeTypeByCollection(string $mimeType, ?string $collection = null, bool $withTrashed = false): Collection
    {
        $collection = $collection ?? config('media.default_collection');

        return $this->applyIndexOrder(
            $this->media()
                ->where('mimetype', $mimeType)
                ->where('collection', $collection)
                ->when($withTrashed, fn ($q) => $q->withTrashed())
        )->get();
    }

    /**
     * Get media by approval status.
     *
     * @param  bool  $withTrashed  Include soft deleted media
     */
    public function mediaApproved(bool $approved = true, bool $withTrashed = false): Collection
    {
        return $this->applyIndexOrder(
            $this->media()
                ->where('approved', $approved)
                ->when($withTrashed, fn ($q) => $q->withTrashed())
        )->get();
    }

    /**
     * Check if the model has media in a specific collection.
     */
    public function hasMedia(?string $collectionName = null, bool $withTrashed = false): bool
    {
        $collectionName = $collectionName ?? config('media.default_collection');

        return $this->media()
            ->where('collection', $collectionName)
            ->when($withTrashed, fn ($q) => $q->withTrashed())
            ->exists();
    }

    /**
     * Get all media.
     */
    public function getMedia(): Collection
    {
        return $this->applyIndexOrder($this->media())->get();
    }

    /**
     * Get the first media.
     */
    public function getFirstMedia(): ?Media
    {
        return $this->media()->oldest()->first();
    }

    /**
     * Get the first media by collection.
     */
    public function getFirstMediaByCollection(?string $collection = null): ?Media
    {
        $collection = $collection ?? config('media.default_collection');

        return $this->media()->where('collection', $collection)->oldest()->first();
    }

    /**
     * Get the last media.
     */
    public function getLastMedia(): ?Media
    {
        return $this->media()->latest()->first();
    }

    /**
     * Get the last media by collection.
     */
    public function getLastMediaByCollection(?string $collection = null): ?Media
    {
        $collection = $collection ?? config('media.default_collection');

        return $this->media()->where('collection', $collection)->latest()->first();
    }

    /**
     * Get the media collection.
     */
    public function getCollection(?string $name = null): Collection|Media|null
    {
        $name = $name ?? config('media.default_collection');
        $register = $this->registerCollections()[$name] ?? null;

        if (! $register) {
            return collect();
        }

        $query = $this->media()->where('collection', $name);

        $query = $this->applyIndexOrder($query);

        return ($register['single'] ?? false) ? $query->first() : $query->get();
    }

    /**
     * Get the media collection as an array.
     */
    public function getCollectionArray(?string $name = null): array
    {
        $name = $name ?? config('media.default_collection');

        return $this->getCollection($name)?->toArray() ?? [];
    }

    /**
     * Get registered collection groups (grouped collections by their 'group' attribute).
     */
    public function getCollectionGroups(array $only = [], array $except = []): array
    {
        $collections = $this->registerCollections();
        $result = [];

        foreach ($collections as $name => $options) {
            if ($this->shouldSkipCollection($name, $only, $except)) {
                continue;
            }

            $result[$name] = $this->formatCollectionMedia($name, $options);
        }

        return $result;
    }

    /**
     * Determine if a collection should be skipped based on filters.
     */
    private function shouldSkipCollection(string $name, array $only, array $except): bool
    {
        if (filled($only) && ! in_array($name, $only)) {
            return true;
        }

        if (filled($except) && in_array($name, $except)) {
            return true;
        }

        return false;
    }

    /**
     * Format collection media based on single/multiple configuration.
     */
    private function formatCollectionMedia(string $name, array $options): array|Media|null
    {
        $media = $this->getCollection($name);

        if (($options['single'] ?? false) === true) {
            return $media ?? [];
        }

        if ($media instanceof Collection) {
            return $media->all();
        }

        return blank($media) ? [] : [$media];
    }

    /**
     * Get the media collection as a single media.
     */
    public function getCollectionUrls(?string $name = null): Collection|string|null
    {
        $name = $name ?? config('media.default_collection');
        $isSingle = $this->registerCollections()[$name]['single'] ?? false;

        $collection = $this->getCollection($name);

        if (blank($collection)) {
            return $isSingle ? null : collect();
        }

        if ($isSingle) {
            return $collection->full_url;
        }

        return $collection->map(function (Media $media) {
            return $media->full_url;
        });
    }

    public function getCollectionGroupUrls(array $only = [], array $except = []): Collection
    {
        $collections = $this->registerCollections();
        $result = collect();

        foreach ($collections as $name => $options) {
            if ($this->shouldSkipCollection($name, $only, $except)) {
                continue;
            }

            $result->push($this->getCollectionUrls($name));
        }

        return $result->flatten();
    }

    /**
     * Get the media relationship.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(config('media.model'), 'mediable', 'mediable_type', 'mediable_id');
    }

    /**
     * Register media collections.
     */
    public function registerCollections(array $attributes = []): array
    {
        if (filled($attributes)) {
            $this->registerMediaCollections = $attributes;
        }

        if (filled($this->registerMediaCollections)) {
            return $this->registerMediaCollections;
        }

        return $this->getDefaultCollection();
    }

    protected function getDefaultCollection(): array
    {
        return [
            config('media.default_collection') => [
                'disk' => config('media.disk', 'public'),
                'bucket' => config('media.bucket', 'upload'),
                'label' => null,
                'single' => false,
                's3' => [
                    'ttl_temporary_url' => config('media.s3.default_ttl_temporary_url', 5),
                ],
            ],
        ];
    }
}
