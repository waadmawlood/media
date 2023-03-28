<?php

namespace Waad\Media\DTO;

class FileDTO
{
    public ?string $path;
    public ?string $basename;
    public ?string $filename;
    public int $index;
    public ?string $extension;
    public int $fileSize;
    public ?string $mimeType;
    public ?string $label;
    public ?string $disk;
    public ?string $directory;

    public function __construct($path)
    {
        $this->path = $path;
        $this->basename = basename($this->path);
    }
}
