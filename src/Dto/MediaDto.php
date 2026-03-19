<?php

namespace Waad\Media\Dto;

class MediaDto
{
    public ?string $path;

    public ?string $basename;

    public ?string $filename;

    public int $index;

    public ?string $extension;

    public int $fileSize;

    public ?string $mimeType;

    public ?string $label;

    public ?string $collection;

    public ?string $disk;

    public ?string $bucket;

    public ?array $metadata;

    public function __construct($path)
    {
        $this->path = $path;
        $this->basename = basename($this->path);
    }
}
