<?php

namespace Waad\Media\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileMedia
{
    public $model;
    public $mediaClass;
    public $file;
    public $index;
    public $result = [];
    public $disk;
    public $path;
    public $baseNames = [];

    public function __construct($model)
    {
        $this->model = $model;
        $this->mediaClass = app(config('media.media_class'));
        $this->disk = config('media.disk');
        $this->path = config('media.path');
    }

    // upload files and return information of files saved
    public function uploading($files, $index = 1)
    {
        $this->file = $files;
        $this->index = $index;

        if (is_null($this->file)) {
            return;
        }
        $is_list = $this->isList($this->file);
        $is_list ? $this->uploadMany() : $this->uploadOne();

        return $this->result;
    }

    // upload file
    private function uploadOne()
    {
        return $this->storeFile($this->file, $this->index);
    }

    // upload list files
    private function uploadMany()
    {
        $i = $this->index;
        foreach ($this->file as $file) {
            $this->storeFile($file, $i++);
        }
    }

    // save file to storage
    private function storeFile($file, $index)
    {
        $baseName = $this->randomNameFile($file);
        Storage::disk($this->disk)->putFileAs($this->path, $file, $baseName);
        $fileName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $file_size = filesize($file);

        $this->addResult($baseName, $fileName, $mime, $file_size, $index);
    }

    // add to media DB after saved in storage
    public function storeMedia()
    {
        foreach ($this->result as $information) {
            $media = [
                'base_name' => $information['base_name'],
                'file_name' => $information['file_name'],
                'index' => $information['index'],
                'mime_type' => $information['mime_type'],
                'file_size' => $information['file_size'],
                'model_type' => get_class($this->model),
                'model_id' => $this->model->getKey(),
                'user_id' => $information['user_id'],
            ];
            $this->model->media()->create($media);
            array_push($this->baseNames, $information['base_name']);
        }

        return $this->baseNames;
    }

    // delete media and files
    public function delete($files)
    {
        $medias = $files == null ? $this->model->media : $this->mediaClass->whereIn('id', $this->isList($files) ? $files : [$files])->get();
        $this->file = $medias;
        $is_list = $this->isList($this->file);
        $is_list ? $this->deleteMany($this->file) : $this->deleteOne($this->file);
    }

    // to method destory file
    private function deleteOne($file)
    {
        return $this->destory($file);
    }

    // to method destory list files
    private function deleteMany($files)
    {
        foreach ($files as $file) {
            $this->destory($file);
        }
    }

    // delete file from storage if delete_file is true in config/media.php
    private function destory($file)
    {
        $delete = true;
        if (config('media.delete_file', false)) {
            if(filled($file)){
                $delete = Storage::disk($this->disk)->delete(sprintf('%s%s%s', $this->path, DIRECTORY_SEPARATOR ,$file['base_name']));
            }
        }
        $file->delete();

        return $delete;
    }

    // add information file saved in result list
    protected function addResult($baseName, $filename, $mime, $file_size, $index)
    {
        $user = auth()->user();
        $this->result[] = [
            'base_name' => $baseName,
            'file_name' => $filename,
            'mime_type' => $mime,
            'file_size' => $file_size,
            'index' => $index,
            'user_id' => is_null($user) ? null : $user->getKey(),
        ];
    }

    // check is list array or no
    private function isList($file)
    {
        if (is_array($file) || ($file instanceof \ArrayAccess && $file instanceof \Traversable)) {
            return true;
        }
        return false;
    }

    // create random string for name of file
    private function randomNameFile($file, $length = 16)
    {
        return Str::random($length).time().'.'.$file->extension();
    }
}
