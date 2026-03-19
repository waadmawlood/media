<?php

namespace Waad\Media\Contracts;

use Illuminate\Support\Collection;
use Waad\Media\Media;

interface MediaService
{
    public function upload(?string $collection = null, ?string $disk = null): Media|Collection|null;

    public function sync(?string $collection = null, ?string $disk = null): Media|Collection|null;

    public function collection(string $collection): static;

    public function disk(string $disk): static;

    public function bucket(string $bucket): static;

    public function label(string $label): static;

    public function index(int $index): static;

    public function fileExists(string $path): bool;

    public function deleteFile(string $path): bool;

    public function fileSize(string $path): ?int;

    public function fileMetadata(string $path): ?array;

    public function temporaryUrl(string $path, int $minutes = 5): ?string;
}
