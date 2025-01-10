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

        $path = $file->store($bucket, ['disk' => $disk]);

        if (! $path) {
            return null;
        }

        $fileDto = new MediaDto($path);
        $fileDto->filename = $file->getClientOriginalName();
        $fileDto->extension = $file->extension();
        $fileDto->fileSize = $file->getSize();
        $fileDto->mimeType = $file->getMimeType();

        $mimeType = explode('/', $fileDto->mimeType)[0];

        if ($mimeType === 'image') {
            try {
                $dimensions = @getimagesize($file->getRealPath());
                if ($dimensions) {
                    $fileDto->metadata = [
                        'width' => $dimensions[0],
                        'height' => $dimensions[1],
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to get image dimensions: '.$e->getMessage());
            } finally {
                // Clean up memory
                if (isset($dimensions)) {
                    unset($dimensions);
                }
                gc_collect_cycles();
            }
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
