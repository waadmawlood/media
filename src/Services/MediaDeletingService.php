<?php

namespace Waad\Media\Services;

use Waad\Media\Media;
use Illuminate\Support\Collection;

class MediaDeletingService extends MediaService
{

    private bool $isList;
    private array $medias;

    public function __construct($model, $files = null)
    {
        parent::__construct($model, $files);
        $this->medias = $this->getModel()->media->pluck('id')->toArray();
    }

    /**
     * remove
     *
     * @return bool|Collection|null
     */
    public function delete()
    {
        if (blank($this->getFiles()))
            return null;

        $this->isList = $this->isList();

        return $this->removeFiles();
    }


    /**
     * Remove Files
     *
     * @return bool|Collection|null
     */
    public function removeFiles()
    {
        return $this->isList ?
            $this->removeManyFiles() :
            $this->removeOneFile();
    }

    /**
     * Upload One File
     *
     * @param Media|int|null $file
     * @return bool|null
     */
    private function removeOneFile(Media|int|null $file = null)
    {
        $media = $file ?? $this->getFiles();

        if (blank($media))
            return null;

        if ($media instanceof Media)
            $media = $media->id;

        return $this->removeMedia($media);
    }

    /**
     * Remove Many Files
     *
     * @return Collection
     */
    private function removeManyFiles()
    {
        foreach ($this->getFiles() as $file) {
            $media = $this->removeOneFile($file);

            if ($media)
                $this->setResult($media);
        }

        return $this->getResult();
    }

    /**
     * Remove Media
     *
     * @return bool|null
     */
    private function removeMedia($id)
    {
        if (!in_array($id, $this->medias))
            return null;

        return $this->getModel()->media()->where('id', $id)->delete();
    }
}
