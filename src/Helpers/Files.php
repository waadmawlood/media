<?php

namespace Waad\Media\Helpers;

use Waad\Media\DTO\FileDTO;
use Illuminate\Http\UploadedFile;

class Files
{
    /**
     * upload File
     *
     * @param UploadedFile $file
     * @param string $direction
     * @param string $disk
     * @return FileDTO|null
     */
    public static function uploadFile(UploadedFile $file, string $direction = 'upload', string $disk = 'public')
    {
        if ($file->isValid()) {
            $path = $file->store($direction, $disk);

            if (!$path)
                return null;

            $fileDto = new FileDTO($path);
            $fileDto->filename = $file->getClientOriginalName();
            $fileDto->extension = $file->extension();
            $fileDto->fileSize = $file->getSize();
            $fileDto->mimeType = $file->getMimeType();

            return $fileDto;
        }

        return null;
    }
}
