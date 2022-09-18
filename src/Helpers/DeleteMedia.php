<?php

namespace Waad\Media\Helpers;

use Waad\Media\Helpers\FileMedia;

class DeleteMedia extends FileMedia
{

    protected $mediaModel;
    public function __construct($files = null, $classParent)
    {
        $this->mediaModel = config('media.media_class');
        $medias = $files == null ? $classParent->media : $this->mediaModel->whereIn('id', $files)->get();
        $this->file = $medias;
        if(config('media.delete_file', false)){
            $this->delete();
        }
        $this->destroy();
    }

    protected function destroy()
    {
        $this->file->delete();
    }
}
