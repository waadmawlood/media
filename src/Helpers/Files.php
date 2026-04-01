<?php

namespace Waad\Media\Helpers;

use Illuminate\Http\UploadedFile;
use Waad\Media\Dto\MediaDto;

class Files
{
    /**
     * Upload file and return MediaDto with metadata
     */
    public static function uploadFile(UploadedFile $file, string $bucket = 'upload', string $disk = 'public'): ?MediaDto
    {
        if (! $file->isValid()) {
            return null;
        }

        // Livewire's TemporaryUploadedFile moves the temp file on store(); read metadata first
        // or getSize()/getRealPath() will target a path that no longer exists (Flysystem UnableToRetrieveMetadata).
        $filename = $file->getClientOriginalName();
        $extension = $file->extension();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $mimeMain = explode('/', $mimeType)[0];

        $imageMetadata = [];
        if ($mimeMain === 'image') {
            try {
                $dimensions = @getimagesize($file->getRealPath());
                if ($dimensions) {
                    $imageMetadata = [
                        'width' => $dimensions[0],
                        'height' => $dimensions[1],
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to get image dimensions: '.$e->getMessage());
            } finally {
                if (isset($dimensions)) {
                    unset($dimensions);
                }
                gc_collect_cycles();
            }
        }

        $path = $file->store($bucket, ['disk' => $disk]);

        if (! $path) {
            return null;
        }

        $fileDto = new MediaDto($path);
        $fileDto->filename = $filename;
        $fileDto->extension = $extension;
        $fileDto->fileSize = $fileSize;
        $fileDto->mimeType = $mimeType;

        if ($imageMetadata !== []) {
            $fileDto->metadata = $imageMetadata;
        }

        return $fileDto;
    }

    public static function getFileMetadata(string $mimeType, string $path): ?array
    {
        if ($mimeType !== 'image') {
            return null;
        }

        $dimensions = getimagesize($path);
        if (! $dimensions) {
            return null;
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'mime' => $dimensions['mime'],
            'bits' => $dimensions['bits'] ?? null,
            'channels' => $dimensions['channels'] ?? null,
        ];
    }
}
