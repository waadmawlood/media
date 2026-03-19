<?php

namespace Waad\Media\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MediaPrunableService
{
    private $query;

    private $dateSubDays;

    private ?Collection $deletedMedia = null;

    private array $pathsGroupedByDisk = [];

    public function __construct($query, $dateSubDays)
    {
        $this->query = $query;
        $this->dateSubDays = $dateSubDays;
    }

    /**
     * Fetch all soft-deleted media files older than the specified date.
     *
     * @return $this
     */
    public function all(): self
    {
        $this->deletedMedia = $this->query
            ->select('disk', 'path')
            ->where('deleted_at', '<', $this->dateSubDays)
            ->whereNotNull('deleted_at')
            ->get();

        return $this;
    }

    /**
     * Group file paths by disk for batch deletion.
     *
     * @return $this
     */
    public function paths(): self
    {
        if ($this->deletedMedia === null) {
            return $this;
        }

        $this->pathsGroupedByDisk = $this->deletedMedia
            ->groupBy('disk')
            ->map(fn ($files) => $files->pluck('path')->all())
            ->all();

        return $this;
    }

    /**
     * Delete all grouped files from their respective disks.
     *
     * @return $this
     */
    public function delete(): self
    {
        foreach ($this->pathsGroupedByDisk as $disk => $paths) {
            if (filled($paths)) {
                Storage::disk($disk)->delete($paths);
            }
        }

        return $this;
    }

    /**
     * Execute the full pruning process: fetch, group, and delete.
     *
     * @return $this
     */
    public function prune(): self
    {
        return $this->all()->paths()->delete();
    }
}
