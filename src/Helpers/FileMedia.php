<?php

namespace Waad\Media\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class FileMedia
{
    protected $model;
    protected $file;
    protected $index;
    protected $destinationPath;
    protected $result = [];
    protected $disk;
    protected $path;


    public function __construct()
    {
        $this->disk = config('media.disk');
        $this->path = config('media.path');
    }

    public function uploading()
    {
        if (! $this->file) {
            return;
        }
        $is_list = $this->isList($this->file);
        $is_list ? $this->uploadMany($this->file) : $this->uploadOne($this->file);

        return $this->result;
    }

    protected function uploadOne($file)
    {
        $this->store($file, $this->index);
    }

    protected function uploadMany($files)
    {
        $i = $this->index;
        foreach ($files as $file) {
            $this->store($file, $i++);
        }
    }

    protected function store($file, $index)
    {
        $baseName = $this->randomNameFile($file);
        Storage::disk($this->disk)->putFileAs($this->path,$file,$baseName);
        $fileName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $file_size = filesize($file);

        $this->addResult($baseName, $fileName, $mime, $file_size, $index);
    }

    public function delete()
    {
        $is_list = $this->isList($this->file);
        return $is_list ? $this->deleteMany($this->file) : $this->deleteOne($this->file);
    }

    protected function deleteOne($file)
    {
        return $this->destory($file);
    }

    protected function deleteMany($files)
    {
        foreach ($files as $file) {
            $this->destory($file);
        }
    }

    protected function destory($file)
    {
        return Storage::disk($this->disk)->delete(sprintf("%s/%s",[$this->path, $file->base_name]));
    }

    public function addResult($baseName, $filename, $mime, $file_size, $index)
    {
        $this->result[] = [
            'basename' => $baseName,
            'filename' => $filename,
            'mime' => $mime,
            'file_size' => $file_size,
            'index' => $index,
        ];
    }

    protected function isList($file)
    {
        return is_array($file);
    }

    protected function randomNameFile($file, $length = 16)
    {
        return Str::random($length).time().'.'.$file->extension();
    }
}
