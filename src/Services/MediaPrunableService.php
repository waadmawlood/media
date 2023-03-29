<?php

namespace Waad\Media\Services;

class MediaPrunableService
{
    private $model;
    private $dateSubDays;
    private $allFiles;
    private array $allPaths;

    public function __construct($model, $dateSubDays)
    {
        $this->model = $model;
        $this->dateSubDays = $dateSubDays;
        $this->allPaths = array();
    }

    /**
     * all
     *
     * @return MediaPrunableService
     */
    public function all()
    {
        $this->allFiles = $this->model
            ->select('disk', 'path')
            ->where('deleted_at', '<', $this->dateSubDays)
            ->whereNotNull('deleted_at')
            ->get();

        return $this;
    }


    /**
     * paths
     *
     * @return MediaPrunableService
     */
    public function paths()
    {
        foreach ($this->allFiles as $file) {
            $path = $this->getRootDisk($file->disk) . DIRECTORY_SEPARATOR . $file->path;
            $this->allPaths[] = $path;
        }

        return $this;
    }

    /**
     * delete
     *
     * @return MediaPrunableService
     */
    public function delete()
    {
        foreach($this->allPaths as $path){
            if(file_exists($path)){
                unlink($path);
            }
        }

        return $this;
    }

    /**
     * Get Root Disk
     *
     * @param string $disk
     * @return string
     */
    private function getRootDisk(string $disk)
    {
        $configDisk = config("filesystems.disks.{$disk}", null);

        if (blank($configDisk) || ! is_array($configDisk))
            return storage_path($disk);

        if(! array_key_exists('root', $configDisk))
            return storage_path($disk);

        return $configDisk['root'];
    }
}
